<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private AdminService $admin) {}

    public function index(): View
    {
        $groups = $this->admin->settingsGrouped();

        return view('admin.settings.index', compact('groups'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'logo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:5120'],
            'favicon_file' => ['nullable', 'mimes:png,jpg,jpeg,svg,webp,ico', 'max:2048'],
        ], [
            'logo_file.max' => 'The logo may not be larger than 5 MB.',
            'favicon_file.max' => 'The favicon may not be larger than 2 MB.',
        ]);

        $this->admin->updateSettings($request->input('settings', []));
        $this->admin->updateBranding(
            $request->file('logo_file'),
            $request->file('favicon_file'),
            $request->boolean('remove_logo'),
            $request->boolean('remove_favicon'),
        );

        $this->flashSuccess('Settings saved.');

        return redirect()->route('admin.settings.index');
    }
}
