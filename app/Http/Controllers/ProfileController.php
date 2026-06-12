<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Repositories\Auth\UserRepository;
use App\Services\Security\SecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private UserRepository $userRepo,
        private SecurityService $security,
    ) {}

    public function index(Request $request): View
    {
        return view('profile.index', ['user' => $request->user()]);
    }

    /** Self-service security overview: recent activity + last sign-in. */
    public function security(Request $request): View
    {
        $logs = $this->security->forUser($request->user(), 25);

        return view('profile.security', ['user' => $request->user(), 'logs' => $logs]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('avatar');

        if ($request->hasFile('avatar')) {
            $user = $request->user();
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $this->userRepo->updateProfile($request->user(), $data);

        $this->flashSuccess('Profile updated successfully.');

        return back();
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        $this->security->recordPasswordChanged($request->user());

        $this->flashSuccess('Password changed successfully.');

        return back();
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate(['avatar' => ['required', 'image', 'max:2048']]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        $this->flashSuccess('Avatar updated.');

        return back();
    }
}
