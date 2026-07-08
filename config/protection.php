<?php

use App\Enums\TrustTier;

return [

    /*
    |--------------------------------------------------------------------------
    | Rolling chargeback reserve
    |--------------------------------------------------------------------------
    | On every escrow release, this percentage of the vendor's earnings is held
    | back in a non-spendable reserve as a buffer against future chargebacks,
    | and paid out automatically after `reserve_days`. Set percent to 0 to
    | disable the reserve entirely (default — opt-in). Admin-overridable via the
    | `protection` settings group (chargeback_reserve_percent / _days).
    */
    'reserve_percent' => 0,     // 0–100
    'reserve_days'    => 30,    // calendar days a reserve is held before payout

    /*
    |--------------------------------------------------------------------------
    | Dispute SLA windows (hours)
    |--------------------------------------------------------------------------
    | response_hours   — how long a party (usually the vendor) has to respond
    |                    before the dispute is auto-escalated for staff review.
    | admin_sla_hours  — how long staff have to act on an active dispute before
    |                    it is auto-escalated to the senior queue.
    | auto_refund_on_breach — if true, a vendor who misses their response window
    |                    triggers an automatic refund-to-buyer instead of a mere
    |                    escalation. Default false (safe: escalate only).
    */
    'dispute_response_hours'  => 48,
    'dispute_admin_sla_hours' => 72,
    'dispute_auto_refund_on_breach' => false,

    /*
    |--------------------------------------------------------------------------
    | Strikes → auto-suspend
    |--------------------------------------------------------------------------
    | A vendor accumulates a strike when they lose a dispute (refund-to-buyer)
    | or a chargeback. Reaching this many active strikes auto-suspends the store
    | (Status::Suspended). Admin-overridable via `strike_suspend_threshold`.
    */
    'strike_suspend_threshold' => 3,

    /*
    |--------------------------------------------------------------------------
    | Buyer abuse strikes → flag / block (serial "fake buyer" protection)
    |--------------------------------------------------------------------------
    | The buyer-side mirror of vendor strikes. A buyer accumulates a strike when
    | a dispute THEY raised is resolved fully in the seller's favour, or a
    | chargeback THEY filed is won by the merchant (a friendly-fraud signal).
    |
    |   buyer_flag_threshold    — active strikes that auto-flag the account for
    |                             admin review (soft; does not block anything).
    |   buyer_suspend_threshold — active strikes that block the buyer from making
    |                             new purchases and opening new disputes until an
    |                             admin lifts it (or strikes are cleared below it).
    |
    | Admin-overridable via `buyer_flag_threshold` / `buyer_suspend_threshold`.
    */
    'buyer_flag_threshold'    => 2,
    'buyer_suspend_threshold' => 4,

    /*
    |--------------------------------------------------------------------------
    | Trust-tier limits
    |--------------------------------------------------------------------------
    | Per-tier guardrails resolved by TrustTier::limits(). All money values are
    | in KOBO. `null` means "no limit" (unlimited). New/low-trust sellers are
    | held to tighter caps and longer escrow windows; they graduate as their
    | trust_score rises (see App\Enums\TrustTier::fromScore).
    |
    |   withdrawal_cap_daily  — max total withdrawal requested per calendar day
    |   escrow_release_days   — business-day escrow hold for physical/product orders
    |   max_active_listings   — max simultaneously-active products + services
    */
    'tiers' => [
        TrustTier::New->value => [
            'withdrawal_cap_daily' => 10_000_000,   // ₦100,000
            'escrow_release_days'  => 7,
            'max_active_listings'  => 5,
        ],
        TrustTier::Rising->value => [
            'withdrawal_cap_daily' => 50_000_000,   // ₦500,000
            'escrow_release_days'  => 5,
            'max_active_listings'  => 20,
        ],
        TrustTier::Trusted->value => [
            'withdrawal_cap_daily' => 200_000_000,  // ₦2,000,000
            'escrow_release_days'  => 3,
            'max_active_listings'  => 100,
        ],
        TrustTier::TopRated->value => [
            'withdrawal_cap_daily' => null,         // unlimited
            'escrow_release_days'  => 2,
            'max_active_listings'  => null,         // unlimited
        ],
    ],
];
