<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    protected $settingService;

    /**
     * __construct
     *
     * @param  mixed  $settingService
     * @return void
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * index
     *
     * @param  mixed  $request
     * @return void
     */
    public function index(Request $request)
    {
        $settings = $this->settingService->list(request());

        $request = request()->all();
        $keyword = $request['keyword'] ?? '';

        return Inertia::render('Admin/General', compact('settings', 'request', 'keyword'));
    }

    /**
     * create
     *
     * @return void
     */
    public function create()
    {
        $setting = new Setting;

        return Inertia::render('Admin/Setting/GeneralCreate', compact('setting'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $setting = Setting::where('id', $id)->firstOrFail();

        return inertia('Admin/Setting/GeneralCreate', compact('setting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $setting = Setting::where('id', $id)->firstOrFail();

        return inertia('Admin/Setting/GeneralCreate', compact('setting'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->settingService->upsert($request->all());

        return redirect()->route('admin.settings.index')->with('success', 'Setting saved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $data['id'] = $id;
        $this->settingService->upsert($data);

        return redirect()->route('admin.settings.index')->with('success', 'Setting updated successfully.');
    }

    /**
     * destroy
     *
     * @param  mixed  $setting
     * @return void
     */
    public function destroy(Setting $setting)
    {
        $setting->delete();

        return to_route('admin.settings.index')->with('success', 'Setting deleted successfully.');
    }
}
