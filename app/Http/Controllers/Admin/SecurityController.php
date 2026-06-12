<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Security\SecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function __construct(private SecurityService $security) {}

    public function index(Request $request): View
    {
        $filters = $request->only('event', 'search');
        $logs    = $this->security->recent($filters);
        $stats   = $this->security->stats();
        $locked  = $this->security->lockedAccounts();

        return view('admin.security.index', compact('logs', 'stats', 'locked', 'filters'));
    }

    public function unlock(User $user): RedirectResponse
    {
        $this->security->unlock($user);
        $this->flashSuccess("{$user->name}'s account has been unlocked.");

        return back();
    }
}
