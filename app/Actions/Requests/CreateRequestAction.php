<?php

namespace App\Actions\Requests;

use App\Enums\RequestStatus;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CreateRequestAction
{
    public function execute(User $buyer, array $data): ProductRequest
    {
        $attachments = [];
        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                if ($file instanceof UploadedFile) {
                    $attachments[] = $file->store('requests/attachments', 'public');
                }
            }
        }

        return ProductRequest::create([
            'buyer_id'    => $buyer->id,
            'category_id' => $data['category_id'] ?? null,
            'title'       => $data['title'],
            'description' => $data['description'],
            'budget_min'  => !empty($data['budget_min'])  ? to_kobo($data['budget_min'])  : null,
            'budget_max'  => !empty($data['budget_max'])  ? to_kobo($data['budget_max'])  : null,
            'attachments' => $attachments ?: null,
            'deadline_at' => !empty($data['deadline_at']) ? $data['deadline_at']          : null,
            'status'      => RequestStatus::Open,
            'is_public'   => $data['is_public'] ?? true,
            'location'    => $data['location'] ?? null,
        ]);
    }
}
