<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Http\Requests\SignupRequest;
use App\Mail\UserCredentialsMail;
use App\Models\Country;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Question;
use App\Models\Speciality;
use App\Models\State;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserVerify;
use App\Services\EmailNotificationService;
use App\Services\InAppNotificationService;
use App\Services\PatientService;
use App\Services\RazorpayPaymentService;
use App\Services\UserService;
use App\Services\UserSubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SignupController extends Controller
{
    protected $userService;

    protected $patientService;

    /**
     * __construct
     *
     * @param  mixed  $userService
     * @param  mixed  $patientService
     * @return void
     */
    public function __construct(UserService $userService, PatientService $patientService)
    {
        // hasAccess('manage doctors');
        $this->userService = $userService;
        $this->patientService = $patientService;
        $this->userService->role = ['Admin', 'Doctor'];
    }

    /**
     * doctor
     */
    public function index(Request $request): Response
    {
        $currency = $this->getUserLocation();

        $subscriptionPlans = SubscriptionPlan::where('status', true)

            ->where('currency', $currency['currency'] ?? 'USD')
            ->select('id', 'title', 'price', 'currency', 'frequency', 'plan_for')
            ->orderBy('price', 'ASC')
            ->get();

        return Inertia::render('Signup/Index', [
            'specialities' => Speciality::select('id', 'name')->orderBy('name')->get()->unique('name')->values(),
            'questions' => Question::orderBy('question', 'ASC')->get(),
            'subscription_plans' => $subscriptionPlans,
        ]);
    }

    public function show($type = null): Response
    {
        if ($type === 'patient') {
            return Inertia::render('Signup/Patient', [
                'token' => null,
                'patient' => null,
                'states' => State::select('id', 'name')->get(),
                'countries' => Country::select('id', 'name')->get(),
                'questions' => Question::orderBy('question', 'ASC')->get(),
            ]);
        }

        return Inertia::render('Signup/Index', [
            'specialities' => Speciality::select('id', 'name')->orderBy('name')->get()->unique('name')->values(),
            'states' => State::select('id', 'name')->get(),
            'countries' => Country::select('id', 'name')->get(),
            'questions' => Question::orderBy('question', 'ASC')->get(),
            'subscription_plan_id' => null,
            'subscription_plans' => \App\Models\SubscriptionPlan::where('status', true)
                ->select('id', 'title', 'price', 'currency', 'frequency', 'plan_for')
                ->orderBy('price', 'ASC')
                ->get(),
        ]);
    }

    public function RegisterPatient(Request $request): Response
    {
        $patient = Patient::where('registration_code', $request->input('token'))->first();

        return Inertia::render('Signup/Patient', [
            'token' => $request->input('token'),
            'patient' => '',
            'states' => State::select('id', 'name')->get(),
            'countries' => Country::select('id', 'name')->get(),
            'questions' => Question::orderBy('question', 'ASC')->get(),

        ]);
    }

    public function storePatient(PatientRequest $request)
    {

        $this->userService->role = 'Patient';
        $user = $this->userService->upsert($request);

        return redirect()->route('login')->with('success', 'Patient created successfully. Verification link sent to email.');
    }

    /**
     * signUp
     *
     * @param  mixed  $request
     * @return void
     */
    public function store(SignupRequest $request)
    {
        return $this->handlePaidSubscriptionSignup($request);
    }

    /**
     * Handle signup for paid subscription plans
     *
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    protected function handlePaidSubscriptionSignup(SignupRequest $request)
    {
        $subscriptionPlanId = $request->input('subscription_plan_id');
        // STEP 1: Create user as inactive
        $userData = $request->all();
        $userData['is_active'] = false; // User starts inactive for paid plans
        $user = $this->userService->upsert($userData);

        if (! $user->id) {
            return Redirect::back()->withErrors(['error' => 'Failed to create account. Please try again.']);
        }

        // Get the subscription plan
        $subscriptionPlan = \App\Models\SubscriptionPlan::find($subscriptionPlanId);

        \Illuminate\Support\Facades\Log::info('Subscription signup attempt', [
            'plan_id' => $subscriptionPlanId,
            'plan_found' => (bool) $subscriptionPlan,
            'plan_title' => $subscriptionPlan?->title,
            'user_id' => $user->id ?? null,
        ]);

        if (! $subscriptionPlan) {
            if (isset($user) && $user->id) {
                $user->delete();
            }

            return Redirect::back()->withErrors(['error' => 'Invalid subscription plan selected. Please try again.']);
        }

        // If starter plan, create subscription with 14-day free trial and activate user
        $planTitle = strtolower($subscriptionPlan->title ?? '');
        \Illuminate\Support\Facades\Log::info('Checking trial plan', [
            'plan_title' => $subscriptionPlan->title,
            'lowercase_title' => $planTitle,
            'is_trial' => $planTitle === 'trial',
        ]);

        if ($planTitle === 'trial') {
            \Illuminate\Support\Facades\Log::info('Trial plan detected, creating subscription', [
                'user_id' => $user->id,
                'plan_id' => $subscriptionPlan->id,
            ]);

            $subscriptionService = new \App\Services\UserSubscriptionService;

            // Create active subscription with 14-day free trial
            $startDate = \Carbon\Carbon::now();
            $endDate = $startDate->copy()->addDays(14); // 14-day free trial

            $subscription = $subscriptionService->createPendingSubscription($user, $subscriptionPlan, [
                'status' => 'active',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'payment_status' => 'free',
            ]);

            \Illuminate\Support\Facades\Log::info('Trial subscription created', [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
            ]);

            // Activate user
            $user->update(['is_active' => true]);

            \Illuminate\Support\Facades\Log::info('User activated', ['user_id' => $user->id]);

            // Create invoice for the trial subscription
            try {
                $invoiceService = new \App\Services\InvoiceService(new \App\Services\RazorpayInvoiceService);
                $invoice = $invoiceService->createSubscriptionInvoice($subscription);
                \Illuminate\Support\Facades\Log::info('Invoice created for trial subscription', [
                    'invoice_id' => $invoice->id,
                    'subscription_id' => $subscription->id,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to create invoice for trial subscription', [
                    'subscription_id' => $subscription->id ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return Redirect::route('login', [
                'status' => 'Your account has been created successfully! Enjoy your 14-day free trial.',
            ]);
        }

        // Create pending subscription for paid plans. The local trial starts immediately
        // after mandate authorization succeeds, while the actual recurring charge starts in 14 days.
        $subscriptionService = new UserSubscriptionService;
        $trialStartDate = Carbon::now();
        $trialEndDate = $trialStartDate->copy()->addDays(14);

        $subscription = $subscriptionService->createPendingSubscription($user, $subscriptionPlan, [
            'start_date' => $trialStartDate->toDateString(),
            'end_date' => $trialEndDate->toDateString(),
            'currency' => $subscriptionPlan->currency ?? 'INR',
            'payment_status' => 'pending',
        ]);

        // Create invoice for the first post-trial cycle.
        try {
            $invoiceService = new \App\Services\InvoiceService(new \App\Services\RazorpayInvoiceService);
            $invoice = $invoiceService->createSubscriptionInvoice($subscription);
            \Illuminate\Support\Facades\Log::info('Invoice created for trial subscription', [
                'invoice_id' => $invoice->id,
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create invoice for trial subscription', [
                'subscription_id' => $subscription->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // STEP 2: Create Razorpay recurring subscription for mandate authorization.
        $razorpayService = new RazorpayPaymentService;
        $razorpayPlanResponse = $razorpayService->createOrGetSubscriptionPlan($subscriptionPlan);

        if (! $razorpayPlanResponse['success']) {
            $subscription->delete();
            $user->delete();

            return Redirect::route('signup')->withErrors(['error' => 'Failed to create payment subscription. Please try again.']);
        }

        $razorpaySubscriptionResponse = $razorpayService->createSubscription([
            'plan_id' => $razorpayPlanResponse['data']['id'],
            'total_count' => $this->getRazorpayTotalCount($subscriptionPlan->frequency),
            'start_at' => $trialEndDate->timestamp,
            'expire_by' => now()->addDay()->timestamp,
            'notes' => [
                'subscription_id' => (string) $subscription->id,
                'user_id' => (string) $user->id,
                'subscription_plan_id' => (string) $subscriptionPlan->id,
                'trial_ends_at' => $trialEndDate->toIso8601String(),
            ],
        ]);

        if (! $razorpaySubscriptionResponse['success']) {
            $subscription->delete();
            $user->delete();

            return Redirect::route('signup')->withErrors(['error' => 'Failed to create payment subscription. Please try again.']);
        }

        $subscription->update([
            'razorpay_subscription_id' => $razorpaySubscriptionResponse['data']['id'],
        ]);

        return Redirect::route('signup.payment.show', $subscription->id);
    }

    /**
     * Resend activation link
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resendActivation(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->is_email_verified) {
            return back()->with('success', 'Your account is already verified. Please login.');
        }

        $token = Str::random(40);
        UserVerify::updateOrCreate(
            ['user_id' => $user->id],
            ['token' => $token]
        );

        app(EmailNotificationService::class)->queueVerificationEmail($user, $token);

        return back()->with('success', 'Activation link sent successfully. Please check your email.');
    }

    public function showPayment(string $subscriptionId): Response|\Illuminate\Http\RedirectResponse
    {
        $subscription = \App\Models\UserSubscription::with(['user', 'subscriptionPlan'])
            ->findOrFail($subscriptionId);
        $razorpaySubscriptionUrl = null;

        if ($subscription->status === 'active' && $subscription->user?->is_active) {
            return Redirect::route('login', [
                'status' => 'We have sent you a verification email. Please verify your email. Your 14-day free trial has started.',
            ]);
        }

        if ($subscription->razorpay_subscription_id) {
            $subscriptionResponse = (new RazorpayPaymentService)->fetchSubscription($subscription->razorpay_subscription_id);
            if ($subscriptionResponse['success']) {
                $razorpaySubscriptionUrl = $subscriptionResponse['data']['short_url'] ?? null;
            }
        }

        return Inertia::render('Signup/Payment', [
            'user' => $subscription->user,
            'subscription' => $subscription,
            'subscriptionPlan' => $subscription->subscriptionPlan,
            'razorpaySubscriptionId' => $subscription->razorpay_subscription_id,
            'razorpaySubscriptionUrl' => $razorpaySubscriptionUrl,
            'razorpayKey' => config('services.razorpay.key'),
            'trialEndsAt' => optional($subscription->end_date)->format('M d, Y'),
        ]);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function verifyAccount(string $token)
    {
        $verifyUser = UserVerify::where('token', $token)->first();

        if (! $verifyUser || ! $verifyUser->user) {
            return redirect()
                ->route('login')
                ->with('error', 'Sorry, your email cannot be identified.');
        }

        $user = $verifyUser->user;

        // Already verified
        if ($user->is_email_verified) {
            return redirect()
                ->route('login')
                ->with('success', 'Your e-mail is already verified. You can now login.');
        }

        // Verify email
        $user->update([
            'is_email_verified' => true,
            'email_verified_at' => now(),
        ]);

        $password = null;
        $cacheKey = 'signup_password_'.$user->id;

        // Get and remove password from cache (one-time use)
        if ($cachedValue = Cache::pull($cacheKey)) {
            try {
                $password = decrypt($cachedValue);
            } catch (\Throwable $e) {
                Log::error('Failed to decrypt signup password', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send credentials email if password exists
        if ($password) {
            try {
                Mail::to($user->email)->send(
                    new UserCredentialsMail([
                        'name' => $user->name,
                        'email' => $user->email,
                        'password' => $password,
                    ])
                );
            } catch (\Throwable $e) {
                // rollback verification if mail fails
                $user->update([
                    'is_email_verified' => false,
                ]);

                Log::error(
                    'Failed to send credentials email after verification',
                    [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }

        app(EmailNotificationService::class)->queueWelcomeEmail($user);

        return redirect()
            ->route('login')
            ->with('success', 'Your e-mail is verified. Check your email for login credentials.');
    }

    /**
     * umaAuth
     *
     * @return void
     */
    public function umaAuth()
    {
        if (Auth::check()) {
            return redirect()->route('patient.dashboard');
        }

        return Inertia::render('Signup/UmaAuth');
    }

    /**
     * Verify payment and activate user
     * STEP 4A: Payment Success → Activate User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_subscription_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'subscription_id' => 'required|uuid',
        ]);

        try {
            $subscription = \App\Models\UserSubscription::with(['user', 'subscriptionPlan'])
                ->findOrFail($request->subscription_id);

            if (
                $subscription->status === 'active'
                && $subscription->user?->is_active
                && $subscription->razorpay_subscription_id === $request->razorpay_subscription_id
            ) {
                return response()->json([
                    'success' => true,
                    'message' => 'We have sent you a verification email. Please verify your email. Your 14-day free trial has started.',
                ]);
            }

            $razorpayService = new RazorpayPaymentService;
            $isValid = $razorpayService->verifySubscriptionSignature([
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_subscription_id' => $request->razorpay_subscription_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);

            if (! $isValid) {
                \Illuminate\Support\Facades\Log::error('Subscription signature verification failed', [
                    'subscription_id' => $subscription->id,
                    'razorpay_subscription_id' => $request->razorpay_subscription_id,
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment signature. Please contact support.',
                ], 400);
            }

            if ($subscription->razorpay_subscription_id !== $request->razorpay_subscription_id) {
                \Illuminate\Support\Facades\Log::error('Payment verification - Subscription ID mismatch', [
                    'subscription_id' => $subscription->id,
                    'stored_subscription_id' => $subscription->razorpay_subscription_id,
                    'received_subscription_id' => $request->razorpay_subscription_id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Subscription ID mismatch. Please contact support.',
                ], 400);
            }

            $trialEndDate = Carbon::now()->addDays(14);

            // Activate the local trial immediately after successful mandate authorization.
            $oldSubscription = clone $subscription;
            $subscription->update([
                'status' => 'active',
                'start_date' => Carbon::now()->toDateString(),
                'end_date' => $trialEndDate->toDateString(),
                'payment_status' => 'pending',
                'razorpay_subscription_id' => $request->razorpay_subscription_id,
            ]);
            app(\App\Services\AuditService::class)->logUpdate(
                'Subscription',
                $oldSubscription,
                $subscription->fresh(),
                'Subscription mandate authorized after signup'
            );

            // Activate user
            $user = $subscription->user;
            $user->update(['is_active' => true]);

            app(InAppNotificationService::class)->notifySuperAdmins(
                app(InAppNotificationService::class)->buildPayload(
                    'New subscription purchased',
                    ($user->name ?? 'A user').' completed mandate authorization for '.($subscription->subscriptionPlan?->title ?? 'a subscription').'.',
                    'subscription_purchased',
                    [
                        'related_model_type' => \App\Models\UserSubscription::class,
                        'related_model_id' => $subscription->id,
                        'meta' => [
                            'plan' => $subscription->subscriptionPlan?->title,
                            'amount' => $subscription->amount,
                            'currency' => $subscription->currency,
                            'trial_ends_at' => $trialEndDate->toDateString(),
                        ],
                    ]
                )
            );

            return response()->json([
                'success' => true,
                'message' => 'We have sent you a verification email. Please verify your email. Your 14-day free trial has started.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Illuminate\Support\Facades\Log::error('Payment verification - Subscription not found', [
                'subscription_id' => $request->subscription_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Subscription not found. Please contact support.',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Payment verification - Validation error', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid payment data. Please try again.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Payment verification failed', [
                'subscription_id' => $request->subscription_id ?? null,
                'razorpay_subscription_id' => $request->razorpay_subscription_id ?? null,
                'razorpay_payment_id' => $request->razorpay_payment_id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            app(InAppNotificationService::class)->notifySuperAdmins(
                app(InAppNotificationService::class)->buildPayload(
                    'Payment gateway failure',
                    'Signup payment authorization failed for subscription '.$request->subscription_id.'.',
                    'payment_gateway_failure',
                    [
                        'meta' => [
                            'subscription_id' => $request->subscription_id,
                            'razorpay_subscription_id' => $request->razorpay_subscription_id,
                            'error' => $e->getMessage(),
                        ],
                    ]
                )
            );

            // Return user-friendly error message without exposing technical details
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Please try again or contact support if the problem persists.',
            ], 500);
        }
    }

    /**
     * Handle payment failure - delete user and data
     * STEP 4B: Payment Failed → Delete User & Data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentFailed(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|uuid',
        ]);

        try {
            $subscription = \App\Models\UserSubscription::with('user')->findOrFail($request->subscription_id);
            $user = $subscription->user;

            // Clear password cache
            $cacheKey = 'subscription_password_'.$subscription->id;
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }

            // Delete subscription
            $subscription->delete();

            // Delete user and all related data (cascade should handle most)
            $user->delete();

            \Illuminate\Support\Facades\Log::info('Payment failed - user and data deleted', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User data has been cleaned up',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found',
            ], 404);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Payment failure handler error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment failure',
            ], 500);
        }
    }

    /**
     * Handle payment cancellation - delete user and data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentCancel(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|uuid',
        ]);

        try {
            $subscription = \App\Models\UserSubscription::with('user')->findOrFail($request->subscription_id);
            $user = $subscription->user;

            // Clear password cache
            $cacheKey = 'subscription_password_'.$subscription->id;
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }

            // Delete subscription
            $subscription->delete();
            if ($user) {
                // Delete user and all related data
                $user->delete();

                // Delete all related data
                Doctor::where('user_id', $user->id)->delete();
                Hospital::where('user_id', $user->id)->delete();
            }

            \Illuminate\Support\Facades\Log::info('Payment cancelled - user and data deleted', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User data has been cleaned up',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found',
            ], 404);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Payment cancellation handler error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment cancellation',
            ], 500);
        }
    }

    private function getRazorpayTotalCount(?string $frequency): int
    {
        return match (strtolower($frequency ?? 'monthly')) {
            'yearly', 'annual', 'annually' => 10,
            default => 120,
        };
    }
}
