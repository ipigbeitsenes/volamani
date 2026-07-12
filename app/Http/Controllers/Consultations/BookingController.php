<?php

namespace App\Http\Controllers\Consultations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultations\BookConsultationRequest;
use App\Models\ConsultantProfile;
use App\Models\ConsultationPackage;
use App\Models\ConsultationSession;
use App\Services\Consultations\ConsultationService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private ConsultationService $service) {}

    public function book(ConsultantProfile $consultant)
    {
        abort_unless($consultant->is_available, 404);
        $packages = $consultant->packages;

        return view('marketplace.consultations.book', compact('consultant', 'packages'));
    }

    public function store(BookConsultationRequest $request, ConsultantProfile $consultant)
    {
        $package = ConsultationPackage::findOrFail($request->package_id);

        abort_unless($package->profile_id === $consultant->id, 422, 'Invalid package for this consultant.');
        abort_unless($package->is_active, 422, 'This package is not currently available.');

        $session = $this->service->bookSession(
            $consultant,
            $package,
            auth()->user(),
            $request->validated()
        );

        return redirect()->route('consultations.sessions.show', $session)
            ->with('success', 'Session booked! Complete payment to confirm your slot.');
    }

    public function mySessions()
    {
        $sessions = ConsultationSession::with(['profile.vendor', 'package'])
            ->where('buyer_id', auth()->id())
            ->orderByDesc('scheduled_at')
            ->paginate(10);

        return view('marketplace.consultations.sessions', compact('sessions'));
    }

    public function show(ConsultationSession $session)
    {
        $user = auth()->user();
        $isConsultant = $session->profile->vendor->user_id === $user->id;
        $isBuyer = $session->buyer_id === $user->id;

        abort_unless($isConsultant || $isBuyer, 403);

        $session->load(['profile.vendor', 'package', 'buyer']);

        return view('marketplace.consultations.session_show', compact('session', 'isConsultant', 'isBuyer'));
    }

    public function cancel(Request $request, ConsultationSession $session)
    {
        abort_unless($session->buyer_id === auth()->id(), 403);
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $this->service->cancelSession($session, $request->reason);

        return redirect()->route('consultations.sessions')
            ->with('success', 'Session cancelled.');
    }
}
