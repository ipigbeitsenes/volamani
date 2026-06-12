<?php

namespace Tests\Unit;

use App\Enums\EscrowStatus;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\ServiceOrder;
use Tests\TestCase;

/**
 * Covers the 24-hour support-ticket window rules added for digital purchases.
 * These exercise model logic only (config + in-memory attributes), so no
 * database is required.
 */
class EscrowTicketWindowTest extends TestCase
{
    private function escrow(string $type, EscrowStatus $status, $heldAt): Escrow
    {
        $escrow = new Escrow();
        $escrow->escrowable_type = $type;
        $escrow->status = $status;
        $escrow->held_at = $heldAt;

        return $escrow;
    }

    public function test_product_escrow_within_window_can_raise_ticket(): void
    {
        $escrow = $this->escrow(Order::class, EscrowStatus::Holding, now()->subHour());

        $this->assertTrue($escrow->isProductEscrow());
        $this->assertTrue($escrow->canRaiseTicket());
        $this->assertTrue($escrow->ticketWindowClosesAt()->isFuture());
    }

    public function test_product_escrow_past_window_cannot_raise_ticket(): void
    {
        $escrow = $this->escrow(Order::class, EscrowStatus::Holding, now()->subHours(25));

        $this->assertFalse($escrow->canRaiseTicket());
        $this->assertTrue($escrow->ticketWindowClosesAt()->isPast());
    }

    public function test_settled_product_escrow_cannot_raise_ticket(): void
    {
        $escrow = $this->escrow(Order::class, EscrowStatus::Released, now()->subHour());

        $this->assertFalse($escrow->canRaiseTicket());
    }

    public function test_service_escrow_ignores_the_24h_window(): void
    {
        $escrow = $this->escrow(ServiceOrder::class, EscrowStatus::Holding, now()->subHours(72));

        $this->assertFalse($escrow->isProductEscrow());
        $this->assertNull($escrow->ticketWindowClosesAt());
        $this->assertTrue($escrow->canRaiseTicket()); // falls back to canDispute()
    }
}
