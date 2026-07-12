<?php

namespace App\Http\Controllers\Returns;

use App\Http\Controllers\Controller;
use App\Http\Requests\Returns\RequestReturnRequest;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Services\Returns\ReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function __construct(private ReturnService $returns) {}

    public function index(Request $request): View
    {
        return view('marketplace.returns.index', [
            'returns' => $this->returns->forBuyer($request->user()),
        ]);
    }

    public function store(RequestReturnRequest $request, Order $order): RedirectResponse
    {
        abort_unless($order->buyer_id === auth()->id(), 403);

        $data = $request->safe()->only(['reason', 'description']);
        $data['photos'] = $request->file('photos', []);

        $this->returns->request($order, $request->user(), $data);
        $this->flashSuccess('Return requested. The seller will review it shortly.');

        return redirect()->route('orders.show', $order);
    }

    public function markShipped(Request $request, ReturnRequest $return): RedirectResponse
    {
        abort_unless($return->buyer_id === auth()->id(), 403);

        $data = $request->validate(['return_tracking' => ['nullable', 'string', 'max:120']]);

        $this->returns->markShipped($return, $data['return_tracking'] ?? null);
        $this->flashSuccess('Thanks — we\'ve let the seller know the item is on its way back.');

        return back();
    }

    public function cancel(ReturnRequest $return): RedirectResponse
    {
        abort_unless($return->buyer_id === auth()->id(), 403);

        $this->returns->cancel($return);
        $this->flashInfo('Return cancelled.');

        return back();
    }
}
