<?php

namespace Tests\Feature;

use App\Enums\EscrowStatus;
use App\Enums\PaymentStatus;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Cart\CartService;
use Database\Factories\ProductFactory;
use Database\Factories\VendorFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end wallet checkout through the real HTTP routes: cart → process →
 * orders paid, buyer debited exactly once, escrow opened per vendor.
 */
class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function fundedBuyer(int $balance = 1_000_000): User
    {
        $buyer = User::factory()->create();
        Wallet::create(['user_id' => $buyer->id, 'balance' => $balance, 'escrow_balance' => 0]);

        return $buyer;
    }

    public function test_multi_vendor_wallet_checkout_creates_one_paid_order_and_escrow_per_vendor(): void
    {
        $buyer = $this->fundedBuyer(1_000_000);
        $productA = ProductFactory::new()->create(['price' => 100_000]);
        $productB = ProductFactory::new()->create(['price' => 250_000]); // different vendor (factory makes one each)

        $this->actingAs($buyer)->post(route('cart.products.add', $productA))->assertRedirect();
        $this->actingAs($buyer)->post(route('cart.products.add', $productB))->assertRedirect();

        $this->actingAs($buyer)
            ->post(route('cart.process'), ['gateway' => 'wallet'])
            ->assertRedirect(route('orders.index'));

        // One order per vendor, both fully paid.
        $orders = Order::where('buyer_id', $buyer->id)->get();
        $this->assertCount(2, $orders);
        $this->assertTrue($orders->every(fn ($o) => $o->payment_status === PaymentStatus::Success));

        // Buyer debited exactly the basket total.
        $this->assertSame(1_000_000 - 350_000, (int) $buyer->wallet->fresh()->balance);

        // Escrow held per order; vendor earnings = total minus 10% platform fee.
        $escrows = Escrow::all();
        $this->assertCount(2, $escrows);
        $this->assertTrue($escrows->every(fn ($e) => $e->status === EscrowStatus::Holding));
        $this->assertEqualsCanonicalizing(
            [90_000, 225_000],
            $escrows->pluck('vendor_earnings')->map(fn ($v) => (int) $v)->all()
        );

        // Vendor escrow balances carry the pending earnings.
        $this->assertSame(90_000 + 225_000, (int) Wallet::whereNot('user_id', $buyer->id)->sum('escrow_balance'));

        // Product escrows get a business-day auto-release date.
        $this->assertTrue($escrows->every(fn ($e) => $e->auto_release_at !== null && $e->auto_release_at->isFuture()));
    }

    public function test_replaying_checkout_does_not_double_charge(): void
    {
        $buyer = $this->fundedBuyer(500_000);
        $product = ProductFactory::new()->create(['price' => 100_000]);

        $this->actingAs($buyer)->post(route('cart.products.add', $product));
        $this->actingAs($buyer)->post(route('cart.process'), ['gateway' => 'wallet']);

        // Replay: the cart is already cleared, so nothing must change.
        $this->actingAs($buyer)
            ->post(route('cart.process'), ['gateway' => 'wallet'])
            ->assertRedirect(route('cart.index'));

        $this->assertSame(400_000, (int) $buyer->wallet->fresh()->balance);
        $this->assertSame(1, Order::where('buyer_id', $buyer->id)->count());
        $this->assertSame(1, Escrow::count());
    }

    public function test_insufficient_wallet_balance_blocks_checkout_without_side_effects(): void
    {
        $buyer = $this->fundedBuyer(10_000);
        $product = ProductFactory::new()->create(['price' => 100_000]);

        $this->actingAs($buyer)->post(route('cart.products.add', $product));

        $this->actingAs($buyer)
            ->post(route('cart.process'), ['gateway' => 'wallet'])
            ->assertSessionHas('error');

        $this->assertSame(10_000, (int) $buyer->wallet->fresh()->balance);
        $this->assertSame(0, Order::count());
        $this->assertSame(0, Escrow::count());
    }

    public function test_a_vendor_cannot_add_their_own_product_to_the_cart(): void
    {
        $vendor = VendorFactory::new()->create();
        $product = ProductFactory::new()->create(['vendor_id' => $vendor->id]);

        $this->actingAs($vendor->user)
            ->post(route('cart.products.add', $product))
            ->assertSessionHas('error');

        $this->actingAs($vendor->user)->get(route('cart.index'))->assertOk();
        $this->assertSame(0, app(CartService::class)->count());
    }

    public function test_multi_vendor_cart_cannot_use_card_checkout(): void
    {
        $buyer = $this->fundedBuyer();
        $a = ProductFactory::new()->create(['price' => 50_000]);
        $b = ProductFactory::new()->create(['price' => 50_000]);

        $this->actingAs($buyer)->post(route('cart.products.add', $a));
        $this->actingAs($buyer)->post(route('cart.products.add', $b));

        $this->actingAs($buyer)
            ->post(route('cart.process'), ['gateway' => 'paystack'])
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }
}
