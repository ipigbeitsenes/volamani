<?php

namespace App\Http\Controllers;

use App\Enums\ServiceOrderStatus;
use App\Models\FreelanceService;
use App\Models\ServiceOrder;
use App\Models\ServicePackage;
use App\Services\Services\ServiceListingService;
use Illuminate\Http\Request;

class ServiceOrderController extends Controller
{
    public function __construct(private ServiceListingService $serviceManager) {}

    public function index(Request $request)
    {
        $orders = ServiceOrder::with(['service', 'package', 'vendor'])
            ->where('buyer_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('service-orders.index', compact('orders'));
    }

    public function show(Request $request, ServiceOrder $serviceOrder)
    {
        $user = $request->user();
        abort_unless(
            $serviceOrder->buyer_id === $user->id || $serviceOrder->vendor->user_id === $user->id,
            403
        );

        $serviceOrder->load(['service', 'package', 'buyer', 'vendor.user', 'messages.sender']);
        $isBuyer  = $serviceOrder->buyer_id === $user->id;
        $isVendor = $serviceOrder->vendor->user_id === $user->id;

        return view('service-orders.show', compact('serviceOrder', 'isBuyer', 'isVendor'));
    }

    public function submitRequirements(Request $request, ServiceOrder $serviceOrder)
    {
        abort_unless($serviceOrder->buyer_id === $request->user()->id, 403);
        abort_unless($serviceOrder->status === ServiceOrderStatus::Active, 422, 'Requirements already submitted.');

        $request->validate(['requirements' => ['required', 'string', 'min:20']]);

        $this->serviceManager->submitRequirements($serviceOrder, $request->requirements);

        $this->flashSuccess('Requirements submitted. The vendor has been notified.');
        return back();
    }

    public function requestRevision(Request $request, ServiceOrder $serviceOrder)
    {
        abort_unless($serviceOrder->buyer_id === $request->user()->id, 403);
        abort_unless($serviceOrder->canRequestRevision(), 422, 'No revisions remaining.');

        $request->validate(['feedback' => ['required', 'string', 'min:20']]);

        $this->serviceManager->requestRevision($serviceOrder, $request->feedback);

        $this->flashSuccess('Revision requested.');
        return back();
    }

    public function complete(Request $request, ServiceOrder $serviceOrder)
    {
        abort_unless($serviceOrder->buyer_id === $request->user()->id, 403);
        abort_unless($serviceOrder->canAcceptDelivery(), 422);

        $this->serviceManager->acceptDelivery($serviceOrder);

        $this->flashSuccess('Order completed. Thank you!');
        return back();
    }

    public function sendMessage(Request $request, ServiceOrder $serviceOrder)
    {
        $user = $request->user();
        abort_unless(
            $serviceOrder->buyer_id === $user->id || $serviceOrder->vendor->user_id === $user->id,
            403
        );

        $request->validate([
            'message'    => ['required_without:attachment', 'nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:20480'],
        ]);

        $this->serviceManager->sendMessage(
            $serviceOrder,
            $user,
            $request->message ?? '',
            $request->file('attachment')
        );

        return back();
    }
}
