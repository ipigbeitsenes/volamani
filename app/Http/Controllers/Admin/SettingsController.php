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
        $this->admin->updateSettings($request->input('settings', []));
        $this->flashSuccess('Settings saved.');

        return redirect()->route('admin.settings.index');
    }
}
