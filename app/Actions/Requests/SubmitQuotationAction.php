<?php

namespace App\Actions\Requests;

use App\Enums\QuotationStatus;
use App\Models\ProductRequest;
use App\Models\ProductRequestQuotation;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;

class SubmitQuotationAction
{
    public function execute(ProductRequest $request, Vendor $vendor, array $data): ProductRequestQuotation
    {
        abort_unless($request->isOpen(), 422, 'This request is no longer accepting quotations.');
        abort_if($request->hasQuotedBy($vendor), 422, 'You have already submitted a quotation for this request.');
        abort_if($request->buyer_id === $vendor->user_id, 403, 'You cannot quote on your own request.');

        $attachments = [];
        if (! empty($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                if ($file instanceof UploadedFile) {
                    $attachments[] = $file->store('quotations/attachments', 'public');
                }
            }
        }

        $quotation = ProductRequestQuotation::create([
            'request_id' => $request->id,
            'vendor_id' => $vendor->id,
            'price' => to_kobo($data['price']),
            'delivery_days' => $data['delivery_days'],
            'message' => $data['message'],
            'attachments' => $attachments ?: null,
            'status' => QuotationStatus::Pending,
        ]);

        $request->increment('quotations_count');

        return $quotation;
    }
}
