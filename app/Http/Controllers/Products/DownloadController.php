<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductFile;
use App\Services\Products\DownloadService;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function __construct(private DownloadService $downloadService) {}

    public function download(Request $request, Order $order, ProductFile $productFile)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'This download link has expired or is invalid.');
        }

        $user = $request->user();

        abort_unless($order->buyer_id === $user->id, 403);
        abort_unless($order->isPaid(), 403, 'Order has not been paid.');

        $belongsToOrder = $order->items()
            ->where('product_id', $productFile->product_id)
            ->exists();

        abort_unless($belongsToOrder, 403, 'This file does not belong to your order.');
        abort_if($productFile->product?->isPhysical(), 403, 'Physical products are not downloadable.');

        return $this->downloadService->serveFile($order, $productFile, $user);
    }

    public function generateLink(Request $request, Order $order, ProductFile $productFile)
    {
        $user = $request->user();

        abort_unless($order->buyer_id === $user->id, 403);
        abort_unless($order->isPaid(), 403);

        $belongsToOrder = $order->items()
            ->where('product_id', $productFile->product_id)
            ->exists();

        abort_unless($belongsToOrder, 403);
        abort_if($productFile->product?->isPhysical(), 403, 'Physical products are not downloadable.');

        $link = $this->downloadService->generateLink($order, $productFile, $user);

        return response()->json(['url' => $link]);
    }
}
