<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultations\CreateConsultantProfileRequest;
use App\Http\Requests\Consultations\CreatePackageRequest;
use App\Http\Requests\Consultations\UpdateConsultantProfileRequest;
use App\Models\ConsultantProfile;
use App\Models\ConsultationPackage;
use App\Models\ConsultationSession;
use App\Services\Consultations\ConsultationService;
use Illuminate\Http\Request;

class ConsultationManagementController extends Controller
{
    public function __construct(private ConsultationService $service) {}

    public function index()
    {
        $vendor = auth()->user()->vendor;
        $profile = $vendor->consultantProfile;

        return view('vendor.consultations.index', compact('vendor', 'profile'));
    }

    public function setup()
    {
        $vendor = auth()->user()->vendor;

        if ($vendor->consultantProfile()->exists()) {
            return redirect()->route('vendor.consultations.profile');
        }

        return view('vendor.consultations.setup');
    }

    public function storeProfile(CreateConsultantProfileRequest $request)
    {
        $profile = $this->service->createProfile(
            auth()->user()->vendor,
            $request->validated()
        );

        return redirect()->route('vendor.consultations.packages')
            ->with('success', 'Consultant profile created. Now add your packages.');
    }

    public function editProfile()
    {
        $profile = auth()->user()->vendor?->consultantProfile;
        if (! $profile) {
            return redirect()->route('vendor.consultations.setup');
        }

        return view('vendor.consultations.profile', compact('profile'));
    }

    public function updateProfile(UpdateConsultantProfileRequest $request, ConsultantProfile $profile)
    {
        $this->service->updateProfile($profile, $request->validated());

        return redirect()->route('vendor.consultations.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function packages()
    {
        $profile = auth()->user()->vendor?->consultantProfile;
        if (! $profile) {
            return redirect()->route('vendor.consultations.setup');
        }

        $packages = $profile->allPackages()->get();

        return view('vendor.consultations.packages', compact('profile', 'packages'));
    }

    public function storePackage(CreatePackageRequest $request)
    {
        $profile = auth()->user()->vendor->consultantProfile;
        $this->service->addPackage($profile, $request->validated());

        return redirect()->route('vendor.consultations.packages')
            ->with('success', 'Package added.');
    }

    public function togglePackage(ConsultationPackage $package)
    {
        $this->authorizePackage($package);
        $this->service->togglePackage($package);

        return back()->with('success', 'Package updated.');
    }

    public function deletePackage(ConsultationPackage $package)
    {
        $this->authorizePackage($package);
        $this->service->deletePackage($package);

        return back()->with('success', 'Package deleted.');
    }

    public function schedule()
    {
        $profile = auth()->user()->vendor?->consultantProfile;
        if (! $profile) {
            return redirect()->route('vendor.consultations.setup');
        }

        $availability = $profile->availabilityByDay();

        return view('vendor.consultations.schedule', compact('profile', 'availability'));
    }

    public function updateSchedule(UpdateConsultantProfileRequest $request, ConsultantProfile $profile)
    {
        $this->service->updateProfile($profile, ['availability' => $request->input('availability', [])]);

        return redirect()->route('vendor.consultations.schedule')
            ->with('success', 'Availability updated.');
    }

    public function sessions()
    {
        $profile = auth()->user()->vendor?->consultantProfile;
        if (! $profile) {
            return redirect()->route('vendor.consultations.setup');
        }

        $sessions = ConsultationSession::with(['buyer', 'package'])
            ->where('profile_id', $profile->id)
            ->orderByDesc('scheduled_at')
            ->paginate(15);

        return view('vendor.consultations.sessions.index', compact('sessions', 'profile'));
    }

    public function showSession(ConsultationSession $session)
    {
        $profile = auth()->user()->vendor?->consultantProfile;
        abort_unless($session->profile_id === $profile?->id, 403);

        $session->load(['buyer', 'package']);

        return view('vendor.consultations.sessions.show', compact('session'));
    }

    public function confirmSession(Request $request, ConsultationSession $session)
    {
        $this->authorizeSession($session);
        $request->validate([
            'meeting_link' => ['required', 'url'],
            'meeting_platform' => ['nullable', 'string', 'in:google_meet,zoom,teams,phone,other'],
        ]);

        $this->service->confirmSession($session, $request->meeting_link, $request->meeting_platform);

        return back()->with('success', 'Session confirmed. The buyer has been notified.');
    }

    public function completeSession(Request $request, ConsultationSession $session)
    {
        $this->authorizeSession($session);
        $request->validate(['consultant_notes' => ['nullable', 'string', 'max:3000']]);

        $this->service->completeSession($session, $request->consultant_notes);

        return back()->with('success', 'Session marked as complete.');
    }

    public function cancelSession(Request $request, ConsultationSession $session)
    {
        $this->authorizeSession($session);
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $this->service->cancelSession($session, $request->reason, true);

        return back()->with('success', 'Session cancelled.');
    }

    private function authorizePackage(ConsultationPackage $package): void
    {
        $profile = auth()->user()->vendor?->consultantProfile;
        abort_unless($package->profile_id === $profile?->id, 403);
    }

    private function authorizeSession(ConsultationSession $session): void
    {
        $profile = auth()->user()->vendor?->consultantProfile;
        abort_unless($session->profile_id === $profile?->id, 403);
    }
}
