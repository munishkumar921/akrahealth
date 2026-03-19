<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SAdminFinanceController extends Controller
{
    public function financedashboard()
    {
        return Inertia::render('SAdmin/finance/Dashboard');
    }

    public function transaction(Request $request)
    {
        $keyword = $request->keyword;
        $transactions = UserSubscription::with(['user', 'subscriptionPlan'])
            ->where('payment_status', 'paid')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'user' => $subscription->user->name ?? 'N/A',
                    'status' => ucfirst($subscription->status),
                    'plan_name' => $subscription->subscriptionPlan->title ?? 'N/A',
                    'amount' => $subscription->amount ?? 0,
                    'order_id' => $subscription->razorpay_order_id ?? $subscription->razorpay_subscription_id ?? $subscription->id,
                    'gateway' => $this->getGatewayName($subscription),
                    'created_on' => $subscription->start_date?->format('Y-m-d') ?? $subscription->created_at->format('Y-m-d'),
                    'frequency' => $subscription->subscriptionPlan->frequency ?? 'N/A',
                ];
            });

        return Inertia::render('SAdmin/finance/Transaction', [
            'transactions' => $transactions,
            'keyword' => $keyword,

        ]);
    }

    public function subscribers(Request $request)
    {
        $keyword = $request->keyword;

        $query = UserSubscription::with(['user', 'subscriptionPlan']);

        // Apply status filter if provided
        if ($request->has('status') && ! empty($request->status)) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->get()
            ->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'user' => $subscription->user->name ?? 'N/A',
                    'status' => ucfirst($subscription->status),
                    'plan_name' => $subscription->subscriptionPlan->title ?? 'N/A',
                    'subscribed_on' => $subscription->start_date?->format('Y-m-d') ?? 'N/A',
                    'subscription_id' => $subscription->razorpay_subscription_id ?? $subscription->id,
                    'pricing_plan' => $subscription->formatted_price ?? 'N/A',
                    'next_payment' => $subscription->end_date?->format('Y-m-d') ?? 'N/A',
                ];
            });

        return Inertia::render('SAdmin/finance/Subscribers', [
            'subscribers' => $subscriptions,
            'keyword' => $keyword,
            'current_status_filter' => $request->status ?? '',
        ]);
    }

    private function getGatewayName($subscription)
    {
        // Determine gateway based on available IDs
        if ($subscription->razorpay_subscription_id) {
            return 'Razorpay';
        }

        // Add logic for other gateways if needed
        return 'N/A';
    }

    public function payment()
    {
        return Inertia::render('SAdmin/finance/Payment');
    }

    public function invoice()
    {
        return Inertia::render('SAdmin/finance/Invoice');
    }
}
