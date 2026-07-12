<?php

namespace App\Repositories\Documents;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DocumentRepository
{
    public function forVendor(Vendor $vendor, DocumentType $type, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Document::where('vendor_id', $vendor->id)
            ->where('type', $type)
            ->withCount('items')
            ->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(fn ($q) => $q->where('number', 'like', "%{$term}%")
                ->orWhere('client_name', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$term}%"));
        }

        return $query->paginate($perPage);
    }

    /** Documents addressed to a registered client. */
    public function forClient(User $user, ?DocumentType $type = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Document::where('client_id', $user->id)
            ->whereIn('status', [
                DocumentStatus::Sent, DocumentStatus::Viewed, DocumentStatus::Partial,
                DocumentStatus::Paid, DocumentStatus::Overdue, DocumentStatus::Accepted,
                DocumentStatus::Signed, DocumentStatus::Declined, DocumentStatus::Converted,
            ])
            ->with('vendor')
            ->latest();

        if ($type) {
            $query->where('type', $type);
        }

        return $query->paginate($perPage);
    }

    /** Documents issued by Volamani itself (platform billing). */
    public function forPlatform(?DocumentType $type = null, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Document::whereNull('vendor_id')
            ->withCount('items')
            ->latest();

        if ($type) {
            $query->where('type', $type);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(fn ($q) => $q->where('number', 'like', "%{$term}%")
                ->orWhere('client_name', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$term}%"));
        }

        return $query->paginate($perPage);
    }

    public function platformStats(): array
    {
        $invoices = Document::whereNull('vendor_id')->where('type', DocumentType::Invoice);

        return [
            'outstanding' => (int) (clone $invoices)
                ->whereIn('status', [DocumentStatus::Sent, DocumentStatus::Viewed, DocumentStatus::Partial, DocumentStatus::Overdue])
                ->sum(DB::raw('total - amount_paid')),
            'paid_total' => (int) (clone $invoices)->where('status', DocumentStatus::Paid)->sum('total'),
            'draft_count' => (int) (clone $invoices)->where('status', DocumentStatus::Draft)->count(),
        ];
    }

    public function vendorStats(Vendor $vendor): array
    {
        $invoices = Document::where('vendor_id', $vendor->id)->where('type', DocumentType::Invoice);

        return [
            'outstanding' => (int) (clone $invoices)
                ->whereIn('status', [DocumentStatus::Sent, DocumentStatus::Viewed, DocumentStatus::Partial, DocumentStatus::Overdue])
                ->sum(DB::raw('total - amount_paid')),
            'paid_total' => (int) (clone $invoices)->where('status', DocumentStatus::Paid)->sum('total'),
            'draft_count' => (int) (clone $invoices)->where('status', DocumentStatus::Draft)->count(),
        ];
    }
}
