<?php

/*
|--------------------------------------------------------------------------
| Toggleable platform features
|--------------------------------------------------------------------------
| The single source of truth for admin feature flags. Each entry seeds a
| boolean `feature_{key}` setting (group "features") that admins flip in
| Settings → Features, is read via feature('key'), enforced on routes via the
| `feature:{key}` middleware, and used to hide entry points in the UI.
| Everything defaults ON. Add a feature here + reseed to expose a new toggle.
*/

return [
    'wallet' => ['Wallet', 'In-app wallet: funding, balance and paying from wallet.'],
    'escrow' => ['Escrow (buyer pages)', 'The buyer-facing Escrow menu & pages. The escrow engine keeps protecting payments regardless of this toggle.'],
    'physical_products' => ['Physical products', 'Buying & selling physical goods (shipping checkout).'],
    'digital_products' => ['Digital products', 'Buying & selling digital downloads.'],
    'services' => ['Freelance services', 'Service gigs, packages and service orders.'],
    'consultations' => ['Consultations', 'Booking & managing consultant sessions.'],
    'requests' => ['Buyer requests', 'Reverse marketplace: buyers post requests for quotes.'],
    'matching' => ['Business matching', 'Lead matching between buyers and vendors.'],
    'affiliates' => ['Affiliates & referrals', 'Referral program and affiliate payouts.'],
    'invoices' => ['Invoices & contracts', 'Vendor invoices, quotations, contracts, estimates & client billing.'],
    'subscriptions' => ['Vendor subscriptions', 'Paid vendor subscription plans.'],
    'promoted_listings' => ['Promoted listings', 'Paid promotion of products & services.'],
    'pricing_calculator' => ['Pricing calculator', 'The project pricing estimator tool.'],
    'returns' => ['Returns / RMA', 'The physical-order returns flow.'],
    'messaging' => ['Buyer ↔ seller messaging', 'In-app direct messages between buyers and sellers.'],
];
