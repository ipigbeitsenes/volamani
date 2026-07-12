<?php

namespace App\Services\Products;

use App\Actions\Products\GenerateDownloadLinkAction;
use App\Models\Order;
use App\Models\ProductDownload;
use App\Models\ProductFile;
use App\Models\User;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadService extends BaseService
{
    public function __construct(
        private GenerateDownloadLinkAction $generateLinkAction,
    ) {}

    public function generateLink(Order $order, ProductFile $file, User $user): string
    {
        return $this->generateLinkAction->execute($order, $file, $user);
    }

    public function serveFile(Order $order, ProductFile $file, User $user): StreamedResponse
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

        if (! Storage::disk('private')->exists($file->path)) {
            abort(404, 'File not found.');
        }

        $download->increment('download_count');
        $download->update(['last_downloaded_at' => now(), 'ip_address' => request()->ip()]);

        return Storage::disk('private')->download($file->path, $file->original_name);
    }

    public function getUserDownloads(Order $order, User $user): Collection
    {
        return ProductDownload::with(['file', 'product'])
            ->where('order_id', $order->id)
            ->where('user_id', $user->id)
            ->get();
    }
}
