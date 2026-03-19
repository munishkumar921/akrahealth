<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PaymentPlatform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SAdminPaymentPlatformController extends Controller
{
    /**
     * Display a listing of payment platforms.
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $environment = $request->get('environment', '');
        $status = $request->get('status', '');

        $query = PaymentPlatform::query();

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('code', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('description', 'LIKE', '%'.$keyword.'%');
            });
        }

        if ($environment) {
            $query->where('environment', $environment);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $platforms = $query->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        // Transform data for frontend
        $platforms->getCollection()->transform(function ($platform) {
            return [
                'id' => $platform->id,
                'name' => $platform->name,
                'code' => $platform->code,
                'description' => $platform->description,
                'environment' => $platform->environment,
                'is_active' => $platform->is_active,
                'is_default' => $platform->is_default,
                'supported_currencies' => $platform->supported_currencies ?? [],
                'webhook_url' => $platform->webhook_url,
                'has_credentials' => ! empty($platform->api_key) && ! empty($platform->secret_key),
                'created_at' => $platform->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $platform->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return Inertia::render('SAdmin/PaymentPlatforms', [
            'platforms' => $platforms,
            'filters' => [
                'keyword' => $keyword,
                'environment' => $environment,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Store a newly created payment platform.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_platforms,name',
            'code' => 'required|string|max:50|unique:payment_platforms,code',
            'description' => 'nullable|string|max:1000',
            'api_key' => 'nullable|string|max:500',
            'secret_key' => 'nullable|string|max:500',
            'merchant_id' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|url|max:500',
            'environment' => 'required|in:sandbox,live',
            'settings' => 'nullable|array',
            'supported_currencies' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        try {
            // If setting as default, unset other defaults
            if ($request->has('is_default') && $request->is_default) {
                PaymentPlatform::where('is_default', true)->update(['is_default' => false]);
            }

            $platform = PaymentPlatform::create($validated);

            Log::info('Payment platform created', [
                'id' => $platform->id,
                'name' => $platform->name,
                'code' => $platform->code,
            ]);

            return redirect()->route('admin.payment-platforms.index')
                ->with('success', __('Payment platform created successfully.'));
        } catch (\Exception $e) {
            Log::error('Error creating payment platform: '.$e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create payment platform: '.$e->getMessage()]);
        }
    }

    /**
     * Update the specified payment platform.
     */
    public function update(Request $request, $id)
    {
        $platform = PaymentPlatform::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_platforms,name,'.$id,
            'code' => 'required|string|max:50|unique:payment_platforms,code,'.$id,
            'description' => 'nullable|string|max:1000',
            'api_key' => 'nullable|string|max:500',
            'secret_key' => 'nullable|string|max:500',
            'merchant_id' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|url|max:500',
            'environment' => 'required|in:sandbox,live',
            'settings' => 'nullable|array',
            'supported_currencies' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        try {
            // If setting as default, unset other defaults
            if ($request->has('is_default') && $request->is_default) {
                PaymentPlatform::where('is_default', true)
                    ->where('id', '!=', $id)
                    ->update(['is_default' => false]);
            }

            $platform->update($validated);

            Log::info('Payment platform updated', [
                'id' => $platform->id,
                'name' => $platform->name,
                'code' => $platform->code,
            ]);

            return redirect()->route('admin.payment-platforms.index')
                ->with('success', __('Payment platform updated successfully.'));
        } catch (\Exception $e) {
            Log::error('Error updating payment platform: '.$e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update payment platform: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified payment platform.
     */
    public function destroy($id)
    {
        $platform = PaymentPlatform::findOrFail($id);

        try {
            // Check if platform has transactions
            if ($platform->transactions()->exists()) {
                return redirect()->back()
                    ->withErrors(['error' => 'Cannot delete payment platform with existing transactions.']);
            }

            $name = $platform->name;
            $platform->delete();

            Log::info('Payment platform deleted', [
                'id' => $id,
                'name' => $name,
            ]);

            return redirect()->route('admin.payment-platforms.index')
                ->with('success', __('Payment platform deleted successfully.'));
        } catch (\Exception $e) {
            Log::error('Error deleting payment platform: '.$e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete payment platform: '.$e->getMessage()]);
        }
    }

    /**
     * Toggle platform active status.
     */
    public function toggleStatus(Request $request, $id)
    {
        $platform = PaymentPlatform::findOrFail($id);

        try {
            $platform->update(['is_active' => ! $platform->is_active]);

            return redirect()->back()
                ->with('success', __('Payment platform status updated successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update status: '.$e->getMessage()]);
        }
    }

    /**
     * Set platform as default.
     */
    public function setDefault($id)
    {
        $platform = PaymentPlatform::findOrFail($id);

        try {
            // Unset all other defaults
            PaymentPlatform::where('is_default', true)->update(['is_default' => false]);

            // Set this as default
            $platform->update(['is_default' => true]);

            return redirect()->back()
                ->with('success', __('Payment platform set as default successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to set default: '.$e->getMessage()]);
        }
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return Inertia::render('SAdmin/PaymentPlatformForm', [
            'platform' => new PaymentPlatform,
            'isEditing' => false,
            'title' => 'Add Payment Platform',
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $platform = PaymentPlatform::findOrFail($id);

        return Inertia::render('SAdmin/PaymentPlatformForm', [
            'platform' => $platform,
            'isEditing' => true,
            'title' => 'Edit Payment Platform',
        ]);
    }

    /**
     * Get active payment platforms (API endpoint).
     */
    public function getActivePlatforms()
    {
        $platforms = PaymentPlatform::active()
            ->whereNotNull('api_key')
            ->whereNotNull('secret_key')
            ->get()
            ->map(function ($platform) {
                return [
                    'id' => $platform->id,
                    'name' => $platform->name,
                    'code' => $platform->code,
                    'environment' => $platform->environment,
                    'supported_currencies' => $platform->supported_currencies ?? [],
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $platforms,
        ]);
    }

    /**
     * Get default payment platform (API endpoint).
     */
    public function getDefaultPlatform()
    {
        $platform = PaymentPlatform::default()->first();

        if (! $platform) {
            return response()->json([
                'success' => false,
                'message' => 'No default payment platform configured.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $platform->id,
                'name' => $platform->name,
                'code' => $platform->code,
                'environment' => $platform->environment,
                'merchant_id' => $platform->merchant_id,
                'supported_currencies' => $platform->supported_currencies ?? [],
            ],
        ]);
    }
}
