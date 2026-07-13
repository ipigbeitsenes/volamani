<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Checkout\PhysicalCheckoutService;
use App\Services\Orders\OrderService;
use App\Services\Wallet\WalletService;
use Database\Factories\VendorFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhysicalPodCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedVendor(): Vendor
    {
        return VendorFactory::new()->create(['verified_at' => now()]);
    }

    private function physicalProduct(Vendor $vendor, int $stock = 5): Product
    {
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'kind' => 'physical',
            'name' => 'Test Gadget',
            'description' => str_repeat('detail ', 12),
            'type' => 'template',
            'price' => 50_000,
            'status' => 'active',
        ]);

        $product->physicalDetail()->create([
            'stock_quantity' => $stock,
            'track_inventory' => true,
            'allow_backorder' => false,
            'condition' => 'new',
        ]);

        return $product->fresh();
    }

    /** @return array<string,string> */
    private function address(): array
    {
        return [
            'ship_to_name' => 'John Buyer',
            'ship_to_phone' => '08030000000',
            'ship_to_address' => '1 Main Street',
            'ship_to_city' => 'Lagos',
            'ship_to_state' => 'Lagos',
        ];
    }

    public function test_verified_seller_pod_creates_an_unpaid_order_and_reserves_stock(): void
    {
        $vendor = $this->verifiedVendor();
        $buyer = User::factory()->create();
        $product = $this->physicalProduct($vendor, 5);

        $result = app(PhysicalCheckoutService::class)
            ->place($buyer, $product, null, 2, $this->address(), 'pod');

        $this->assertSame('pod', $result['status']);

        $order = $result['order'];
        $this->assertTrue($order->isPod());
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(OrderStatus::Processing, $order->status);

        // Buyer was charged nothing up front.
        $this->assertSame(0, app(WalletService::class)->getOrCreate($buyer)->balance);

        // Stock was reserved at order time (5 - 2).
        $this->assertSame(3, (int) $product->physicalDetail->fresh()->stock_quantity);
    }

    public function test_unverified_seller_cannot_offer_pay_on_delivery(): void
    {
        $vendor = VendorFactory::new()->create(['verified_at' => null]);
        $buyer = User::factory()->create();
        $product = $this->physicalProduct($vendor, 5);

        $result = app(PhysicalCheckoutService::class)
            ->place($buyer, $product, null, 1, $this->address(), 'pod');

        $this->assertSame('pod_unavailable', $result['status']);
        $this->assertDatabaseCount('orders', 0);
        // Stock untouched.
        $this->assertSame(5, (int) $product->physicalDetail->fresh()->stock_quantity);
    }

    public function test_delivery_settles_commission_from_the_seller_wallet_once(): void
    {
        $vendor = $this->verifiedVendor();
        $buyer = User::factory()->create();
        $product = $this->physicalProduct($vendor, 5);

        $wallets = app(WalletService::class);
        $sellerWallet = $wallets->getOrCreate($vendor->user);
        $wallets->credit($sellerWallet, 100_000, TransactionType::WalletFunding, 'test top-up');

        $order = app(PhysicalCheckoutService::class)
            ->place($buyer, $product, null, 1, $this->address(), 'pod')['order'];

        $commission = (int) $order->platform_fee;
        $this->assertGreaterThan(0, $commission);

        $this->assertTrue(app(OrderService::class)->markDelivered($order));

        $order->refresh();
        $this->assertSame(OrderStatus::Delivered, $order->status);
        $this->assertSame(PaymentStatus::Success, $order->payment_status);
        $this->assertSame(100_000 - $commission, $sellerWallet->fresh()->balance);
        $this->assertDatabaseHas('wallet_ledgers', [
            'wallet_id' => $sellerWallet->id,
            'type' => TransactionType::Commission->value,
            'amount' => $commission,
        ]);

        // Buyer confirming receipt afterwards completes the order without re-charging.
        app(OrderService::class)->markComplete($order->fresh(), $buyer);
        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
        $this->assertSame(100_000 - $commission, $sellerWallet->fresh()->balance);
    }

    public function test_pod_stays_wallet_free_when_the_wallet_feature_is_off(): void
    {
        // Turn the wallet subsystem off — POD is meant to keep working without it.
        Setting::create(['key' => 'feature_wallet', 'value' => '0', 'type' => 'boolean', 'group' => 'features']);
        cache()->forget('settings.feature_wallet');

        $vendor = $this->verifiedVendor();
        $buyer = User::factory()->create();
        $product = $this->physicalProduct($vendor, 5);

        // Even with a funded wallet, the commission must NOT be debited.
        $wallets = app(WalletService::class);
        $sellerWallet = $wallets->getOrCreate($vendor->user);
        $wallets->credit($sellerWallet, 100_000, TransactionType::WalletFunding, 'test top-up');

        $order = app(PhysicalCheckoutService::class)
            ->place($buyer, $product, null, 1, $this->address(), 'pod')['order'];

        app(OrderService::class)->markDelivered($order);

        $order->refresh();
        $this->assertSame(PaymentStatus::Success, $order->payment_status);
        $this->assertSame(100_000, $sellerWallet->fresh()->balance);
        $this->assertStringContainsString('commission', strtolower((string) $order->notes));
        $this->assertDatabaseMissing('wallet_ledgers', [
            'wallet_id' => $sellerWallet->id,
            'type' => TransactionType::Commission->value,
        ]);
    }

    public function test_pod_takes_no_commission_when_wallet_and_escrow_are_both_off(): void
    {
        // Subscription-only mode: both money subsystems disabled.
        Setting::create(['key' => 'feature_wallet', 'value' => '0', 'type' => 'boolean', 'group' => 'features']);
        Setting::create(['key' => 'feature_escrow', 'value' => '0', 'type' => 'boolean', 'group' => 'features']);
        cache()->forget('settings.feature_wallet');
        cache()->forget('settings.feature_escrow');

        $vendor = $this->verifiedVendor();
        $buyer = User::factory()->create();
        $product = $this->physicalProduct($vendor, 5);

        $wallets = app(WalletService::class);
        $sellerWallet = $wallets->getOrCreate($vendor->user);
        $wallets->credit($sellerWallet, 100_000, TransactionType::WalletFunding, 'test top-up');

        $order = app(PhysicalCheckoutService::class)
            ->place($buyer, $product, null, 1, $this->address(), 'pod')['order'];

        app(OrderService::class)->markDelivered($order);

        $order->refresh();
        $this->assertSame(PaymentStatus::Success, $order->payment_status);
        // No commission at all: wallet untouched, nothing owed, no commission note.
        $this->assertSame(100_000, $sellerWallet->fresh()->balance);
        $this->assertStringNotContainsString('commission', strtolower((string) $order->notes));
        $this->assertDatabaseMissing('wallet_ledgers', [
            'wallet_id' => $sellerWallet->id,
            'type' => TransactionType::Commission->value,
        ]);
    }

    public function test_commission_is_recorded_as_owed_when_the_seller_wallet_is_empty(): void
    {
        $vendor = $this->verifiedVendor();
        $buyer = User::factory()->create();
        $product = $this->physicalProduct($vendor, 5);

        $order = app(PhysicalCheckoutService::class)
            ->place($buyer, $product, null, 1, $this->address(), 'pod')['order'];

        app(OrderService::class)->markDelivered($order);

        $order->refresh();
        $this->assertSame(PaymentStatus::Success, $order->payment_status);
        // No balance to debit — stays at zero, debt noted, seller notified.
        $this->assertSame(0, app(WalletService::class)->getOrCreate($vendor->user)->balance);
        $this->assertStringContainsString('commission', strtolower((string) $order->notes));
        $this->assertNotNull($vendor->user->notifications()->first());
    }
}
