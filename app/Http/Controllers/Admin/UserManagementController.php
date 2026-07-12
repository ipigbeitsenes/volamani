<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(private AdminService $admin) {}

    public function index(Request $request): View
    {
        $filters = $request->only('search', 'role', 'status');
        $users = $this->admin->users($filters);

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'vendor', 'wallet']);
        $assignableRoles = AdminService::ASSIGNABLE_ROLES;

        return view('admin.users.show', compact('user', 'assignableRoles'));
    }

    public function updateRoles(Request $request, User $user): RedirectResponse
    {
        if ($this->isProtected($user)) {
            $this->flashError('You cannot change the roles of this account.');

            return back();
        }

        $validated = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', Rule::in(AdminService::ASSIGNABLE_ROLES)],
        ]);

        $this->admin->syncUserRoles($user, $validated['roles'] ?? []);
        $this->flashSuccess('User roles updated.');

        return back();
    }

    public function verify(User $user): RedirectResponse
    {
        if ($this->isProtected($user)) {
            $this->flashError('You cannot modify this account.');

            return back();
        }

        $this->admin->verifyUser($user);
        $this->flashSuccess('User verified.');

        return back();
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        if ($this->isProtected($user)) {
            $this->flashError('You cannot change the status of this account.');

            return back();
        }

        $active = $request->boolean('is_active');
        $this->admin->setUserActive($user, $active);
        $this->flashSuccess($active ? 'User reactivated.' : 'User deactivated.');

        return back();
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($this->isProtected($user)) {
            $this->flashError('You cannot delete this account.');

            return back();
        }

        $this->admin->deleteUser($user);
        $this->flashSuccess('User account removed.');

        return redirect()->route('admin.users.index');
    }

    /** Guard against an admin disabling/deleting themselves or another admin. */
    private function isProtected(User $user): bool
    {
        return $user->id === auth()->id() || $user->hasRole('admin');
    }
}
