<?php

namespace App\Actions\Products;

use App\Enums\ProductStatus;
use App\Enums\TransactionType;
use App\Models\Product;
use App\Models\User;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class PromoteProductAction
{
    public function __construct(private WalletService $walletService) {}

    /**
     * Charge the vendor a flat fee from their wallet to feature a product for a
     * set number of days. Extends the window if it's already promoted. Returns
     * the new "featured until" timestamp; throws on insufficient balance.
     */
    public function execute(Product $product, User $vendorUser): \Illuminate\Support\Carbon
    {
        abort_unless($product->status === ProductStatus::Active, 422, 'Only active products can be promoted.');

        $fee  = (int) config('payment.promotion.fee', 1_000_00);
        $days = (int) config('payment.promotion.days', 7);

        return DB::transaction(function () use ($product, $vendorUser, $fee, $days) {
            $wallet = $this->walletService->getOrCreate($vendorUser);

            abort_unless($wallet->canWithdraw($fee), 422,
                'Insufficient wallet balance to promote. The promotion fee is ' . money($fee) . '.');

            $this->walletService->debit(
                $wallet,
                $fee,
                TransactionType::Debit,
                "Product promotion — {$product->name}",
                $product,
            );

            // Extend from the current end date if still running, else from now.
            $base = $product->isPromoted() ? $product->featured_until : now();
            $until = $base->copy()->addDays($days);

            $product->update([
                'is_featured'    => true,
                'featured_until' => $until,
            ]);

            return $until;
        });
    }
}
