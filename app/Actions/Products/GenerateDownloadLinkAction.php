<?php

namespace App\Actions\Products;

use App\Models\Order;
use App\Models\ProductDownload;
use App\Models\ProductFile;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class GenerateDownloadLinkAction
{
    public function execute(Order $order, ProductFile $file, User $user): string
    {
        $download = ProductDownload::firstOrCreate(
            [
                'order_id' => $order->id,
                'product_file_id' => $file->id,
                'user_id' => $user->id,
            ],
            [
                'product_id' => $file->product_id,
                'download_count' => 0,
            ]
        );

        if ($download->hasReachedLimit()) {
            abort(403, 'Download limit reached for this file.');
        }

        $expiryHours = $file->product->download_expiry_hours
            ?? (int) settings('default_download_expiry_hours', 48);

        return URL::temporarySignedRoute(
            'products.download',
            now()->addHours($expiryHours),
            [
                'order' => $order->id,
                'productFile' => $file->id,
            ]
        );
    }
}
