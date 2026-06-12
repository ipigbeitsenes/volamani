<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Services\Services\ServiceListingService;
use Illuminate\Http\Request;

class ServiceOrderManagementController extends Controller
{
    public function __construct(private ServiceListingService $serviceManager) {}

    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;

        $orders = ServiceOrder::with(['service', 'package', 'buyer'])
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->paginate(15);

        return view('vendor.service-orders.index', compact('orders'));
    }

    public function deliver(Request $request, ServiceOrder $serviceOrder)
    {
        $vendor = $request->user()->vendor;
        abort_unless($serviceOrder->vendor_id === $vendor->id, 403);

        $request->validate([
            'message'    => ['required', 'string', 'min:20'],
            'attachment' => ['nullable', 'file', 'max:51200'],
        ]);

        $this->serviceManager->deliver(
            $serviceOrder,
            $request->message,
            $request->file('attachment')
        );

        $this->flashSuccess('Delivery submitted. The buyer has been notified.');
        return back();
    }

    public function sendMessage(Request $request, ServiceOrder $serviceOrder)
    {
        $vendor = $request->user()->vendor;
        abort_unless($serviceOrder->vendor_id === $vendor->id, 403);

        $request->validate([
            'message'    => ['required_without:attachment', 'nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:20480'],
        ]);

        $this->serviceManager->sendMessage(
            $serviceOrder,
            $request->user(),
            $request->message ?? '',
            $request->file('attachment')
        );

        return back();
    }
}
