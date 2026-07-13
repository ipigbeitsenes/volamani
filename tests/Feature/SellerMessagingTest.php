<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SellerConversation;
use App\Models\User;
use App\Models\Vendor;
use Database\Factories\VendorFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerMessagingTest extends TestCase
{
    use RefreshDatabase;

    private function product(Vendor $vendor): Product
    {
        return Product::create([
            'vendor_id' => $vendor->id,
            'kind' => 'digital',
            'name' => 'Test Product',
            'description' => str_repeat('detail ', 12),
            'type' => 'template',
            'price' => 100_000,
            'status' => 'active',
        ]);
    }

    public function test_buyer_can_message_a_seller_about_a_product(): void
    {
        $vendor = VendorFactory::new()->create();
        $buyer = User::factory()->create();
        $product = $this->product($vendor);

        $this->actingAs($buyer)
            ->post(route('messages.start'), ['product_id' => $product->id, 'body' => 'Is this available?'])
            ->assertRedirect();

        $conversation = SellerConversation::first();
        $this->assertNotNull($conversation);
        $this->assertSame($buyer->id, $conversation->buyer_id);
        $this->assertSame($vendor->id, $conversation->vendor_id);
        $this->assertDatabaseHas('seller_messages', ['sender_id' => $buyer->id, 'body' => 'Is this available?']);

        // The seller received a notification.
        $this->assertNotNull($vendor->user->notifications()->first());
    }

    public function test_seller_can_reply_but_cannot_message_their_own_store(): void
    {
        $vendor = VendorFactory::new()->create();
        $buyer = User::factory()->create();
        $product = $this->product($vendor);

        $this->actingAs($buyer)->post(route('messages.start'), ['product_id' => $product->id, 'body' => 'Hi']);
        $conversation = SellerConversation::first();

        $this->actingAs($vendor->user)
            ->post(route('messages.reply', $conversation), ['body' => 'Yes, it is available'])
            ->assertRedirect();

        $this->assertSame(2, $conversation->messages()->count());

        $this->actingAs($vendor->user)
            ->post(route('messages.start'), ['product_id' => $product->id, 'body' => 'x'])
            ->assertStatus(422);
    }

    public function test_a_non_participant_cannot_view_the_thread(): void
    {
        $vendor = VendorFactory::new()->create();
        $buyer = User::factory()->create();
        $stranger = User::factory()->create();
        $product = $this->product($vendor);

        $this->actingAs($buyer)->post(route('messages.start'), ['product_id' => $product->id, 'body' => 'Hi']);
        $conversation = SellerConversation::first();

        $this->actingAs($stranger)->get(route('messages.show', $conversation))->assertStatus(403);
        $this->actingAs($buyer)->get(route('messages.show', $conversation))->assertOk();
    }
}
