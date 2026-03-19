<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Services\UserSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    protected $userSubscriptionService;

    public function __construct(UserSubscriptionService $userSubscriptionService)
    {
        $this->userSubscriptionService = $userSubscriptionService;
    }

    /**
     * Display a listing of subscription plans.
     */
    public function index()
    {
        $currency = $this->getUserLocation();
        $plans = SubscriptionPlan::where('status', true)->where('currency', $currency['currency'] ?? 'USD')->whereNotIn('title', ['trial', 'Trial'])->get();

        $user = Auth::user();
        $activeSubscription = null;
        $expiredSubscription = null;
        $canUpgrade = false;

        if ($user) {
            $activeSubscription = $this->userSubscriptionService->getActiveSubscriptions($user)->first();
            // Load subscriptionPlan relationship
            if ($activeSubscription) {
                $activeSubscription->load('subscriptionPlan');
            }

            // Check if user has expired subscription and can upgrade
            $canUpgrade = $this->userSubscriptionService->canUpgrade($user);
            if ($canUpgrade) {
                $expiredSubscription = $this->userSubscriptionService->getExpiredSubscription($user);
                if ($expiredSubscription) {
                    $expiredSubscription->load('subscriptionPlan');
                }
            }
        }

        return Inertia::render('Subscriptions/Index', [
            'plans' => $plans,
            'activeSubscription' => $activeSubscription,
            'expiredSubscription' => $expiredSubscription,
            'canUpgrade' => $canUpgrade,
        ]);
    }

    /**
     * Show plan details for subscription/upgrade.
     */
    public function show($planId)
    {
        $plan = SubscriptionPlan::where('status', true)->findOrFail($planId);
        $user = Auth::user();

        $currentSubscription = null;
        $isUpgrade = false;
        $upgradeFrom = '';

        if ($user) {
            // First check for active subscription
            $currentSubscription = $this->userSubscriptionService->getActiveSubscriptions($user)->first();

            // If no active subscription, check for expired subscription (upgrade case)
            if (! $currentSubscription) {
                $canUpgrade = $this->userSubscriptionService->canUpgrade($user);
                if ($canUpgrade) {
                    $currentSubscription = $this->userSubscriptionService->getExpiredSubscription($user);
                    if ($currentSubscription) {
                        $isUpgrade = true;
                        $upgradeFrom = $currentSubscription->subscriptionPlan->title ?? 'Expired Plan';
                        // Load the subscriptionPlan relationship
                        $currentSubscription->load('subscriptionPlan');
                    }
                }
            } else {
                // Load subscriptionPlan relationship for active subscription
                $currentSubscription->load('subscriptionPlan');
            }
        }

        return Inertia::render('Subscriptions/SelectPlan', [
            'plan' => $plan,
            'currentSubscription' => $currentSubscription,
            'isUpgrade' => $isUpgrade,
            'upgradeFrom' => $upgradeFrom,
        ]);
    }

    /**
     * Subscribe user to a plan.
     */
    public function subscribe(Request $request, $planId)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $user = Auth::user();
        $plan = SubscriptionPlan::findOrFail($planId);

        try {
            // Create Razorpay order for the subscription
            $razorpayService = new \App\Services\RazorpayPaymentService;

            $orderData = [
                'amount' => $plan->price,
                'currency' => $plan->currency ?? 'INR',
                'receipt' => 'sub_'.$plan->id.'_'.time(),
                'notes' => [
                    'subscription_plan_id' => $plan->id,
                    'user_id' => $user->id,
                    'type' => 'new_subscription',
                ],
            ];

            $orderResponse = $razorpayService->createOrder($orderData);

            if (! $orderResponse['success']) {
                // Handle the error
                return response()->json([
                    'success' => false,
                    'message' => $orderResponse['error'] ?? 'Failed to create payment order. Please try again.',
                ], 400);
            }

            // Create pending subscription
            $subscription = $this->userSubscriptionService->createPendingSubscription($user, $plan, [
                'razorpay_order_id' => $orderResponse['data']['id'],
                'payment_method_id' => $request->payment_method_id,
            ]);
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

            return response()->json([
                'success' => true,
                'message' => 'Payment order created. Please complete the payment.',
                'subscription' => $subscription,
                'razorpayOrder' => [
                    'id' => $orderResponse['data']['id'],
                    'amount' => $orderResponse['data']['amount'],
                    'currency' => $orderResponse['data']['currency'],
                ],
                'razorpayKey' => config('services.razorpay.key'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Verify subscription payment.
     */
    public function verifyPayment(Request $request, $subscriptionId)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $user = Auth::user();
        $subscription = UserSubscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            // Verify payment signature
            $razorpayService = new \App\Services\RazorpayPaymentService;
            $isValid = $razorpayService->verifyPaymentSignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);

            if (! $isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment signature',
                ], 400);
            }

            // Verify order_id matches
            if ($subscription->razorpay_order_id !== $request->razorpay_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order ID mismatch',
                ], 400);
            }

            // Activate subscription
            $subscription->update([
                'status' => 'active',
                'payment_status' => 'paid',
                'razorpay_payment_id' => $request->razorpay_payment_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Your subscription is now active.',
                'subscription' => $subscription->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription payment verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Please contact support.',
            ], 500);
        }
    }

    /**
     * Cancel user subscription.
     */
    public function cancel($subscriptionId)
    {
        $user = Auth::user();
        $subscription = UserSubscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            $this->userSubscriptionService->cancel($subscription);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show renewal payment page.
     */
    public function showRenewal($subscriptionId)
    {
        $user = Auth::user();
        $subscription = UserSubscription::with('subscriptionPlan')
            ->where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Create Razorpay order for renewal
        $razorpayService = new \App\Services\RazorpayPaymentService;

        $orderData = [
            'amount' => $subscription->subscriptionPlan->price,
            'currency' => $subscription->subscriptionPlan->currency ?? 'INR',
            'receipt' => 'renew_'.$subscription->id.'_'.time(),
            'notes' => [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'subscription_plan_id' => $subscription->subscriptionPlan->id,
                'type' => 'renewal',
            ],
        ];

        $orderResponse = $razorpayService->createOrder($orderData);

        if (! $orderResponse['success']) {
            return redirect()->back()->withErrors(['error' => 'Failed to create payment order. Please try again.']);
        }

        // Store order_id in subscription
        $subscription->update(['razorpay_order_id' => $orderResponse['data']['id']]);

        return Inertia::render('Subscriptions/RenewalPayment', [
            'subscription' => $subscription,
            'subscriptionPlan' => $subscription->subscriptionPlan,
            'razorpayOrder' => $orderResponse['data'],
            'razorpayKey' => config('services.razorpay.key'),
        ]);
    }

    /**
     * Renew user subscription (after payment).
     */
    public function renew($subscriptionId)
    {
        $user = Auth::user();
        $subscription = UserSubscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            $renewedSubscription = $this->userSubscriptionService->renew($subscription);

            return response()->json([
                'success' => true,
                'message' => 'Subscription renewed successfully.',
                'subscription' => $renewedSubscription,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display all user subscriptions (active, expired, cancelled, etc.)
     */
    public function allSubscriptions()
    {
        $user = Auth::user();
        $subscriptions = $this->userSubscriptionService->getAllSubscriptions($user);

        return Inertia::render('Admin/MySubscription/AllSubscriptions', [
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Verify renewal payment.
     */
    public function verifyRenewalPayment(Request $request, $subscriptionId)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $user = Auth::user();
        $subscription = UserSubscription::with('subscriptionPlan')
            ->where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            // Verify payment signature
            $razorpayService = new \App\Services\RazorpayPaymentService;
            $isValid = $razorpayService->verifyPaymentSignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);

            if (! $isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment signature',
                ], 400);
            }

            // Verify order_id matches
            if ($subscription->razorpay_order_id !== $request->razorpay_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order ID mismatch',
                ], 400);
            }

            // Renew subscription
            $renewedSubscription = $this->userSubscriptionService->renew($subscription);

            // Update payment details
            $renewedSubscription->update([
                'payment_status' => 'paid',
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'status' => 'active',
            ]);

            Log::info('Subscription renewal payment successful', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'payment_id' => $request->razorpay_payment_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Your subscription has been renewed.',
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription renewal payment verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Please contact support.',
            ], 500);
        }
    }

    /**
     * Create Razorpay order for renewal.
     */
    public function createRenewalOrder(Request $request, $subscriptionId)
    {
        $user = Auth::user();
        $subscription = UserSubscription::with('subscriptionPlan')
            ->where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $razorpayService = new \App\Services\RazorpayPaymentService;

        $orderData = [
            'amount' => $subscription->subscriptionPlan->price,
            'currency' => $subscription->subscriptionPlan->currency ?? 'INR',
            'receipt' => 'renew_'.$subscription->id.'_'.time(),
            'notes' => [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'type' => 'renewal',
            ],
        ];

        $orderResponse = $razorpayService->createOrder($orderData);

        if (! $orderResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order',
            ], 500);
        }

        // Store order_id in subscription
        $subscription->update(['razorpay_order_id' => $orderResponse['data']['id']]);

        return response()->json([
            'success' => true,
            'order' => $orderResponse['data'],
        ]);
    }

    /**
     * Show upgrade plan page for expired subscription.
     */
    public function showUpgrade($planId)
    {
        $plan = SubscriptionPlan::where('status', true)->findOrFail($planId);
        $user = Auth::user();

        // Get expired subscription
        $expiredSubscription = $this->userSubscriptionService->getExpiredSubscription($user);
        $canUpgrade = $this->userSubscriptionService->canUpgrade($user);

        if (! $canUpgrade || ! $expiredSubscription) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'You cannot upgrade at this time. Please check your subscription status.');
        }

        return Inertia::render('Subscriptions/SelectPlan', [
            'plan' => $plan,
            'currentSubscription' => $expiredSubscription,
            'isUpgrade' => true,
            'upgradeFrom' => $expiredSubscription->subscriptionPlan->title ?? 'Expired Plan',
        ]);
    }

    /**
     * Show upgrade plan page for ACTIVE subscription (new feature).
     */
    public function showActiveUpgrade($planId)
    {
        $plan = SubscriptionPlan::where('status', true)->findOrFail($planId);
        $user = Auth::user();

        // Check if user can upgrade from active subscription
        $canUpgradeFromActive = $this->userSubscriptionService->canUpgradeFromActive($user, $planId);

        if (! $canUpgradeFromActive) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'You cannot upgrade this subscription at this time.');
        }

        // Get current active subscription
        $currentSubscription = $this->userSubscriptionService->getActiveSubscriptions($user)->first();

        if ($currentSubscription) {
            $currentSubscription->load('subscriptionPlan');
        }

        return Inertia::render('Subscriptions/SelectPlan', [
            'plan' => $plan,
            'currentSubscription' => $currentSubscription,
            'isUpgrade' => true,
            'isActiveUpgrade' => true, // New flag for active subscription upgrades
            'upgradeFrom' => $currentSubscription->subscriptionPlan->title ?? 'Current Plan',
        ]);
    }

    /**
     * Upgrade user subscription from expired to new plan.
     */
    public function upgrade(Request $request, $planId)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $user = Auth::user();
        $plan = SubscriptionPlan::where('status', true)->findOrFail($planId);

        Log::info('upgrade method called', [
            'user_id' => $user->id,
            'plan_id' => $planId,
            'plan_title' => $plan->title,
        ]);

        // Check if user can upgrade
        $canUpgrade = $this->userSubscriptionService->canUpgrade($user);
        Log::info('upgrade - canUpgrade check', [
            'user_id' => $user->id,
            'canUpgrade' => $canUpgrade,
        ]);

        if (! $canUpgrade) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot upgrade at this time. Please check your subscription status.',
            ], 400);
        }

        try {
            // Create Razorpay order for the upgrade
            $razorpayService = new \App\Services\RazorpayPaymentService;

            $orderData = [
                'amount' => $plan->price,
                'currency' => $plan->currency ?? 'INR',
                'receipt' => 'upgrade_'.$plan->id.'_'.time(),
                'notes' => [
                    'subscription_plan_id' => $plan->id,
                    'user_id' => $user->id,
                    'type' => 'upgrade',
                ],
            ];

            $orderResponse = $razorpayService->createOrder($orderData);

            if (! $orderResponse['success']) {
                Log::warning('upgrade - failed to create Razorpay order', [
                    'user_id' => $user->id,
                    'error' => $orderResponse['error'] ?? 'Unknown error',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $orderResponse['error'] ?? 'Failed to create payment order. Please try again.',
                ], 400);
            }

            Log::info('upgrade - Razorpay order created', [
                'user_id' => $user->id,
                'razorpay_order_id' => $orderResponse['data']['id'],
            ]);

            // Create pending subscription as upgrade
            $expiredSubscription = $this->userSubscriptionService->getExpiredSubscription($user);
            Log::info('upgrade - expired subscription found', [
                'user_id' => $user->id,
                'expired_subscription_id' => $expiredSubscription->id ?? null,
            ]);

            $subscription = $this->userSubscriptionService->createPendingSubscription($user, $plan, [
                'razorpay_order_id' => $orderResponse['data']['id'],
                'payment_method_id' => $request->payment_method_id,
                'upgraded_from' => $expiredSubscription->id,
                'status' => 'pending_upgrade',
            ]);

            Log::info('upgrade - pending subscription created', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment order created. Please complete the payment to upgrade your subscription.',
                'subscription' => $subscription,
                'razorpayOrder' => [
                    'id' => $orderResponse['data']['id'],
                    'amount' => $orderResponse['data']['amount'],
                    'currency' => $orderResponse['data']['currency'],
                ],
                'razorpayKey' => config('services.razorpay.key'),
            ]);
        } catch (\Exception $e) {
            Log::error('upgrade method failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Verify upgrade payment.
     */
    public function verifyUpgradePayment(Request $request, $subscriptionId)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $user = Auth::user();
        $subscription = UserSubscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        Log::info('verifyUpgradePayment called', [
            'user_id' => $user->id,
            'subscription_id' => $subscriptionId,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_order_id' => $request->razorpay_order_id,
        ]);

        try {
            // Verify payment signature
            $razorpayService = new \App\Services\RazorpayPaymentService;
            $isValid = $razorpayService->verifyPaymentSignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);

            if (! $isValid) {
                Log::warning('verifyUpgradePayment - invalid payment signature', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscriptionId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment signature',
                ], 400);
            }

            // Verify order_id matches
            if ($subscription->razorpay_order_id !== $request->razorpay_order_id) {
                Log::warning('verifyUpgradePayment - order ID mismatch', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscriptionId,
                    'expected_order_id' => $subscription->razorpay_order_id,
                    'received_order_id' => $request->razorpay_order_id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Order ID mismatch',
                ], 400);
            }

            Log::info('verifyUpgradePayment - payment signature verified, fetching expired subscription', [
                'user_id' => $user->id,
                'subscription_id' => $subscriptionId,
            ]);

            // Get the expired subscription
            $expiredSubscription = $this->userSubscriptionService->getExpiredSubscription($user);

            Log::info('verifyUpgradePayment - expired subscription lookup result', [
                'user_id' => $user->id,
                'found' => $expiredSubscription ? true : false,
                'expired_subscription_id' => $expiredSubscription->id ?? null,
                'expired_subscription_status' => $expiredSubscription->status ?? null,
            ]);

            if ($expiredSubscription) {
                Log::info('verifyUpgradePayment - updating old expired subscription to replaced', [
                    'old_subscription_id' => $expiredSubscription->id,
                    'new_subscription_id' => $subscription->id,
                ]);

                // Mark old expired subscription as replaced
                $oldStatus = $expiredSubscription->status;
                $expiredSubscription->update([
                    'status' => 'replaced',
                    'replaced_at' => now()->toDateTimeString(),
                    'replaced_by' => $subscription->id,
                ]);

                Log::info('verifyUpgradePayment - old subscription status updated', [
                    'old_subscription_id' => $expiredSubscription->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'replaced',
                    'replaced_by' => $subscription->id,
                ]);
            } else {
                Log::warning('verifyUpgradePayment - no expired subscription found to replace', [
                    'user_id' => $user->id,
                    'new_subscription_id' => $subscription->id,
                ]);
            }

            // Activate the new subscription
            $subscription->update([
                'status' => 'active',
                'payment_status' => 'paid',
                'razorpay_payment_id' => $request->razorpay_payment_id,
            ]);

            Log::info('Subscription upgrade payment successful', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'old_subscription_id' => $expiredSubscription->id ?? null,
                'old_subscription_status' => $expiredSubscription->status ?? null,
                'new_subscription_status' => 'active',
                'payment_id' => $request->razorpay_payment_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Your subscription has been upgraded successfully.',
                'subscription' => $subscription->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription upgrade payment verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'subscription_id' => $subscriptionId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Please contact support.',
            ], 500);
        }
    }

    /**
     * Upgrade user subscription from ACTIVE subscription to new plan.
     * This handles upgrading while the current subscription is still active.
     */
    public function activeUpgrade(Request $request, $planId)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $user = Auth::user();
        $plan = SubscriptionPlan::where('status', true)->findOrFail($planId);

        Log::info('activeUpgrade method called', [
            'user_id' => $user->id,
            'plan_id' => $planId,
            'plan_title' => $plan->title,
        ]);

        // Check if user can upgrade from active subscription
        $canUpgradeFromActive = $this->userSubscriptionService->canUpgradeFromActive($user, $planId);
        Log::info('activeUpgrade - canUpgradeFromActive check', [
            'user_id' => $user->id,
            'canUpgradeFromActive' => $canUpgradeFromActive,
        ]);

        if (! $canUpgradeFromActive) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot upgrade this subscription at this time.',
            ], 400);
        }

        try {
            // Get current active subscription for reference
            $currentSubscription = $this->userSubscriptionService->getActiveSubscriptions($user)->first();

            // Create Razorpay order for the upgrade
            $razorpayService = new \App\Services\RazorpayPaymentService;

            $orderData = [
                'amount' => $plan->price,
                'currency' => $plan->currency ?? 'INR',
                'receipt' => 'active_upgrade_'.$plan->id.'_'.time(),
                'notes' => [
                    'subscription_plan_id' => $plan->id,
                    'user_id' => $user->id,
                    'type' => 'active_upgrade',
                    'old_subscription_id' => $currentSubscription->id ?? null,
                ],
            ];

            $orderResponse = $razorpayService->createOrder($orderData);

            if (! $orderResponse['success']) {
                Log::warning('activeUpgrade - failed to create Razorpay order', [
                    'user_id' => $user->id,
                    'error' => $orderResponse['error'] ?? 'Unknown error',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $orderResponse['error'] ?? 'Failed to create payment order. Please try again.',
                ], 400);
            }

            Log::info('activeUpgrade - Razorpay order created', [
                'user_id' => $user->id,
                'razorpay_order_id' => $orderResponse['data']['id'],
            ]);

            // Create pending subscription for the upgrade
            $subscription = $this->userSubscriptionService->createPendingSubscription($user, $plan, [
                'razorpay_order_id' => $orderResponse['data']['id'],
                'payment_method_id' => $request->payment_method_id,
                'upgraded_from' => $currentSubscription->id,
                'status' => 'pending_upgrade',
            ]);

            Log::info('activeUpgrade - pending subscription created', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment order created. Please complete the payment to upgrade your subscription.',
                'subscription' => $subscription,
                'razorpayOrder' => [
                    'id' => $orderResponse['data']['id'],
                    'amount' => $orderResponse['data']['amount'],
                    'currency' => $orderResponse['data']['currency'],
                ],
                'razorpayKey' => config('services.razorpay.key'),
            ]);
        } catch (\Exception $e) {
            Log::error('activeUpgrade method failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Verify payment for active subscription upgrade.
     * This marks the old active subscription as 'cancelled' and creates a new pending subscription.
     */
    public function verifyActiveUpgradePayment(Request $request, $subscriptionId)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $user = Auth::user();
        $newSubscription = UserSubscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        Log::info('verifyActiveUpgradePayment called', [
            'user_id' => $user->id,
            'subscription_id' => $subscriptionId,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_order_id' => $request->razorpay_order_id,
        ]);

        try {
            // Verify payment signature
            $razorpayService = new \App\Services\RazorpayPaymentService;
            $isValid = $razorpayService->verifyPaymentSignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);

            if (! $isValid) {
                Log::warning('verifyActiveUpgradePayment - invalid payment signature', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscriptionId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment signature',
                ], 400);
            }

            // Verify order_id matches
            if ($newSubscription->razorpay_order_id !== $request->razorpay_order_id) {
                Log::warning('verifyActiveUpgradePayment - order ID mismatch', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscriptionId,
                    'expected_order_id' => $newSubscription->razorpay_order_id,
                    'received_order_id' => $request->razorpay_order_id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Order ID mismatch',
                ], 400);
            }

            // Complete the upgrade - this activates the new subscription
            $completedSubscription = $this->userSubscriptionService->completeActiveSubscriptionUpgrade($newSubscription);

            Log::info('Active subscription upgrade payment successful', [
                'user_id' => $user->id,
                'new_subscription_id' => $completedSubscription->id,
                'new_plan_id' => $completedSubscription->subscription_plan_id,
                'new_subscription_status' => 'active',
                'payment_id' => $request->razorpay_payment_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Your subscription has been upgraded successfully.',
                'subscription' => $completedSubscription,
            ]);

        } catch (\Exception $e) {
            Log::error('Active subscription upgrade payment verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'subscription_id' => $subscriptionId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Please contact support.',
            ], 500);
        }
    }
}
