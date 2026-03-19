<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LabOrder;
use App\Services\LabOrderService;

class ALabOrderController extends Controller
{
    protected $labOrderService;

    /**
     * __construct
     *
     * @param  mixed  $labOrderService
     * @return void
     */
    public function __construct(LabOrderService $labOrderService)
    {
        $this->labOrderService = $labOrderService;
    }

    /**
     * index
     *
     * @param  mixed  $request
     * @return void
     */
    public function index()
    {
        $orders = $this->labOrderService->list(request());
        $request = request();
        $keyword = $request->get('keyword') ?? '';

        return inertia('Admin/Labs/LabOrdersList', compact('orders', 'request', 'keyword'));
    }

    /**
     * destroy
     *
     * @param  mixed  $id
     * @return void
     */
    public function destroy(string $id)
    {
        LabOrder::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Lab order deleted successfully.');
    }
}
