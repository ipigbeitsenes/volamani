<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case Account = 'account';        // welcome, security, password, login
    case Orders = 'orders';         // product orders & downloads
    case ServiceOrders = 'service_orders'; // freelance order lifecycle + messages
    case Payments = 'payments';       // payments, wallet, withdrawals
    case Escrow = 'escrow';         // escrow holds/releases + disputes
    case Reviews = 'reviews';        // reviews & ratings received
    case Verification = 'verification';   // KYC / vendor verification outcomes
    case Affiliate = 'affiliate';      // referral signups & commissions
    case Subscription = 'subscription';   // plan renewals, expiry, billing
    case Matching = 'matching';       // business matches & leads
    case Invoices = 'invoices';       // invoices & quotations received
    case Social = 'social';         // new listings from sellers you follow
    case Messages = 'messages';       // buyer ↔ seller direct messages
    case Marketing = 'marketing';      // promotions, tips, product updates

    public function label(): string
    {
        return match ($this) {
            self::Account => 'Account & security',
            self::Orders => 'Orders & purchases',
            self::ServiceOrders => 'Service orders',
            self::Payments => 'Payments & wallet',
            self::Escrow => 'Escrow & disputes',
            self::Reviews => 'Reviews & ratings',
            self::Verification => 'KYC & verification',
            self::Affiliate => 'Affiliate & referrals',
            self::Subscription => 'Subscription & billing',
            self::Matching => 'Business matching',
            self::Invoices => 'Invoices & quotations',
            self::Social => 'Sellers you follow',
            self::Messages => 'Messages',
            self::Marketing => 'Promotions & updates',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Account => 'Sign-in alerts and important account changes.',
            self::Orders => 'Order confirmations, payments and download links.',
            self::ServiceOrders => 'Deliveries, revisions and order messages.',
            self::Payments => 'Wallet credits, withdrawals and payment results.',
            self::Escrow => 'Funds held, released and dispute updates.',
            self::Reviews => 'New reviews and ratings on your listings.',
            self::Verification => 'Outcomes of your KYC and vendor verification.',
            self::Affiliate => 'New referrals and commission payouts.',
            self::Subscription => 'Renewals, failed charges and plan changes.',
            self::Matching => 'New matches, leads and connections.',
            self::Invoices => 'Invoices and quotations sent to you.',
            self::Social => 'New products and updates from stores you follow.',
            self::Messages => 'New direct messages from buyers or sellers.',
            self::Marketing => 'Offers, tips and platform announcements.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Account => 'bi-shield-lock',
            self::Orders => 'bi-bag-check',
            self::ServiceOrders => 'bi-briefcase',
            self::Payments => 'bi-wallet2',
            self::Escrow => 'bi-safe',
            self::Reviews => 'bi-star',
            self::Verification => 'bi-patch-check',
            self::Affiliate => 'bi-people',
            self::Subscription => 'bi-arrow-repeat',
            self::Matching => 'bi-diagram-3',
            self::Invoices => 'bi-receipt',
            self::Social => 'bi-person-heart',
            self::Messages => 'bi-chat-dots',
            self::Marketing => 'bi-megaphone',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Account => 'dark',
            self::Orders => 'primary',
            self::ServiceOrders => 'info',
            self::Payments => 'success',
            self::Escrow => 'warning',
            self::Reviews => 'warning',
            self::Verification => 'success',
            self::Affiliate => 'primary',
            self::Subscription => 'info',
            self::Matching => 'primary',
            self::Invoices => 'secondary',
            self::Social => 'danger',
            self::Messages => 'primary',
            self::Marketing => 'secondary',
        };
    }

    /**
     * Essential categories are always delivered in-app and by email — the user
     * cannot opt out (security and legally/operationally important notices).
     */
    public function isEssential(): bool
    {
        return $this === self::Account;
    }

    public function defaultEmail(): bool
    {
        // Marketing is opt-in for email; everything else defaults on.
        return $this !== self::Marketing;
    }

    public function defaultDatabase(): bool
    {
        return true;
    }
}
