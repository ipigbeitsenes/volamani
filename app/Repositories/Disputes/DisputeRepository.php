<?php

namespace App\Repositories\Disputes;

use App\Models\Dispute;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DisputeRepository
{
    /** Disputes the user is a party to (as buyer or as the vendor's owner). */
    public function forUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $vendorId = $user->vendor?->id;

        return Dispute::with(['escrow', 'buyer', 'vendor'])
            ->where(function ($q) use ($user, $vendorId) {
                $q->where('buyer_id', $user->id);
                if ($vendorId) {
                    $q->orWhere('vendor_id', $vendorId);
                }
            })
            ->latest()
            ->paginate($perPage);
    }

    public function allForAdmin(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = Dispute::with(['escrow', 'buyer', 'vendor', 'raisedBy'])->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $query->where('reference', 'like', '%'.$filters['search'].'%');
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Dispute
    {
        return Dispute::with([
            'escrow.escrowable', 'buyer', 'vendor', 'raisedBy', 'resolvedBy',
            'messages.sender',
        ])->find($id);
    }

    public function openCount(): int
    {
        return Dispute::whereIn('status', ['open', 'under_review', 'awaiting_response', 'escalated'])->count();
    }

    /**
     * Open disputes whose awaited-response deadline has elapsed and that have not
     * already been auto-actioned this cycle. Excludes already-escalated ones.
     */
    public function dueForSla()
    {
        return Dispute::with(['escrow', 'vendor'])
            ->whereIn('status', ['open', 'under_review', 'awaiting_response'])
            ->whereNotNull('response_due_at')
            ->where('response_due_at', '<=', now())
            ->where('sla_breached', false)
            ->get();
    }
}
