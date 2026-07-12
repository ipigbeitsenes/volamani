<?php

namespace App\Services\Social;

use App\Actions\Social\ToggleFollowAction;
use App\Enums\NotificationCategory;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\Social\FollowRepository;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FollowService
{
    public function __construct(
        private ToggleFollowAction $toggleAction,
        private FollowRepository $repo,
        private NotificationService $notifications,
    ) {}

    public function toggle(User $user, Vendor $vendor): bool
    {
        return $this->toggleAction->execute($user, $vendor);
    }

    public function isFollowing(?User $user, Vendor $vendor): bool
    {
        return $user !== null && $this->repo->isFollowing($user, $vendor);
    }

    public function followedVendors(User $user, int $perPage = 12): LengthAwarePaginator
    {
        return $this->repo->followedVendors($user, $perPage);
    }

    /**
     * Notify everyone following a vendor that it just launched a new product.
     * Called when a product first becomes publicly active. This is the
     * "social commerce" delivery the concept describes (followers get pinged
     * when a seller launches new products).
     */
    public function announceNewProduct(Product $product): void
    {
        $vendor = $product->vendor;
        if (! $vendor) {
            return;
        }

        $followers = $this->repo->followerUsers($vendor);
        if ($followers->isEmpty()) {
            return;
        }

        $this->notifications->send(
            $followers,
            NotificationCategory::Social,
            "New from {$vendor->business_name}",
            "{$vendor->business_name} just launched a new product: {$product->name}.",
            route('marketplace.products.show', $product->slug),
            'View product',
        );
    }
}
