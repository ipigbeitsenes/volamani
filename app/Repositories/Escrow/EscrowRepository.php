<?php

namespace App\Repositories\Escrow;

use App\Enums\EscrowStatus;
use App\Models\Escrow;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EscrowRepository
{
    public function buyerEscrows(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Escrow::with(['vendor', 'escrowable'])
            ->where('buyer_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function vendorEscrows(Vendor $vendor, int $perPage = 15): LengthAwarePaginator
    {
        return Escrow::with(['buyer', 'escrowable'])
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->paginate($perPage);
    }

    public function allForAdmin(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = Escrow::with(['buyer', 'vendor', 'escrowable'])->latest();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('reference', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Escrow
    {
        return Escrow::with(['buyer', 'vendor', 'payment', 'transactions.actor', 'escrowable'])->find($id);
    }

    /** Holding escrows whose buyer-protection window has elapsed. */
    public function dueForAutoRelease(): Collection
    {
        return Escrow::where('status', EscrowStatus::Holding)
            ->whereNotNull('auto_release_at')
            ->where('auto_release_at', '<=', now())
            ->get();
    }

    /** Total earnings a vendor currently has locked in escrow (kobo). */
    public function heldTotalForVendor(Vendor $vendor): int
    {
        return (int) Escrow::where('vendor_id', $vendor->id)
            ->whereIn('status', [EscrowStatus::Holding, EscrowStatus::PartiallyReleased, EscrowStatus::Disputed])
            ->get()
            ->sum(fn (Escrow $e) => $e->heldAmount());
    }
}
