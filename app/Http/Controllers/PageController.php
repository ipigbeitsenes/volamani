<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public, mostly-static informational pages: About, Contact, Help Center,
 * Seller Guide and the legal documents (privacy, terms, cookies, refunds,
 * disputes). These back the links surfaced in the site footer.
 */
class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function help(): View
    {
        return view('pages.help');
    }

    public function sellerGuide(): View
    {
        return view('pages.seller-guide');
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'contactEmail' => settings('contact_email', 'support@volamani.com'),
            'contactPhone' => settings('contact_phone', '+234 800 000 0000'),
        ]);
    }

    /**
     * Public buyer-protection guarantee page. Copy is admin-editable (the
     * `protection` settings group) and the numbers are pulled live from config /
     * settings so the page never drifts from how the platform actually behaves.
     */
    public function buyerProtection(): View
    {
        $num = fn (string $key, $fallback) => ($v = settings($key)) === null || $v === '' ? $fallback : (int) $v;

        return view('pages.buyer-protection', [
            'intro'          => settings('protection_intro', ''),
            'escrowSummary'  => settings('protection_escrow_summary', ''),
            'returnSummary'  => settings('protection_return_summary', ''),
            'disputeProcess' => settings('protection_dispute_process', ''),
            'chargebackNote' => settings('protection_chargeback_note', ''),
            'supportEmail'   => settings('protection_support_email', settings('support_email', 'support@volamani.com')),
            'returnDays'     => (int) config('business_days.return_window_days', 7),
            'reservePercent' => $num('chargeback_reserve_percent', (int) config('protection.reserve_percent', 0)),
            'responseHours'  => $num('dispute_response_hours', (int) config('protection.dispute_response_hours', 48)),
            'escrowDaysMin'  => \App\Enums\TrustTier::TopRated->escrowReleaseDays(),
            'escrowDaysMax'  => \App\Enums\TrustTier::New->escrowReleaseDays(),
        ]);
    }

    public function contactSubmit(Request $request): RedirectResponse
    {
        $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email', 'max:160'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
        ]);

        // Persisted to the application log so the team can follow up. Wiring a
        // mailable or support-ticket record can replace this later.
        logger()->channel('stack')->info('Contact form submission', $request->only('name', 'email', 'subject', 'message'));

        $this->flashSuccess("Thanks for reaching out, {$request->input('name')}! Our team will get back to you within 1 business day.");

        return redirect()->route('pages.contact');
    }

    /**
     * Render a legal document from a shared template + content map so all of
     * them stay visually consistent and easy to edit in one place.
     */
    public function legal(string $slug): View
    {
        $doc = self::legalContent()[$slug] ?? abort(404);

        return view('pages.legal', ['doc' => $doc, 'slug' => $slug]);
    }

    /**
     * @return array<string, array{title:string, intro:string, sections:array<int, array{heading:string, body:array<int,string>}>}>
     */
    private static function legalContent(): array
    {
        $brand = settings('site_name', 'Volamani');

        return [
            'privacy' => [
                'title'    => 'Privacy Policy',
                'intro'    => "How {$brand} collects, uses and protects your personal information.",
                'sections' => [
                    ['heading' => 'Information we collect', 'body' => [
                        "We collect the details you provide when you create an account, set up a storefront or complete a transaction — including your name, email address, phone number and, where required for verification, identity documents (NIN, BVN or CAC records).",
                        'We also collect technical data such as your IP address, device and browser information, and how you interact with the platform.',
                    ]],
                    ['heading' => 'How we use your information', 'body' => [
                        'To operate your account, process payments and escrow, verify your identity, prevent fraud, provide support and improve the platform.',
                        'We never sell your personal data. We share it only with payment processors (such as Paystack) and service providers strictly necessary to deliver the service, or when required by law.',
                    ]],
                    ['heading' => 'Payment data', 'body' => [
                        'Card and bank details entered during checkout are handled directly by our PCI-compliant payment partners. We do not store full card numbers on our servers.',
                    ]],
                    ['heading' => 'Your rights', 'body' => [
                        'You may access, correct or request deletion of your personal data at any time by contacting us. Note that some records must be retained to meet legal, tax and anti-fraud obligations.',
                    ]],
                    ['heading' => 'Contact', 'body' => [
                        'Questions about this policy? Reach us through the Contact page.',
                    ]],
                ],
            ],
            'terms' => [
                'title'    => 'Terms of Service',
                'intro'    => "The rules for using {$brand} as a buyer or seller.",
                'sections' => [
                    ['heading' => 'Acceptance of terms', 'body' => [
                        "By creating an account or using {$brand}, you agree to these terms. If you do not agree, please do not use the platform.",
                    ]],
                    ['heading' => 'Accounts', 'body' => [
                        'You are responsible for keeping your login credentials secure and for all activity under your account. Sellers may be required to complete KYC verification before withdrawing funds.',
                    ]],
                    ['heading' => 'Buying and selling', 'body' => [
                        'Sellers must accurately describe their products, services and consultations. Buyers must pay through the platform so that escrow protection applies. Off-platform payments are not protected.',
                        'Funds for each order are held in escrow and released to the seller once delivery is confirmed or the protection window elapses.',
                    ]],
                    ['heading' => 'Fees', 'body' => [
                        'We charge a platform commission on completed transactions and may charge withdrawal or subscription fees. Applicable fees are shown before you confirm an action.',
                    ]],
                    ['heading' => 'Prohibited conduct', 'body' => [
                        'No illegal goods, fraud, intellectual-property infringement, or attempts to circumvent escrow and fees. We may suspend accounts that breach these terms.',
                    ]],
                    ['heading' => 'Liability', 'body' => [
                        'The platform is provided "as is". We facilitate transactions between buyers and sellers but are not a party to the underlying contracts between them.',
                    ]],
                ],
            ],
            'cookies' => [
                'title'    => 'Cookie Policy',
                'intro'    => "How {$brand} uses cookies and similar technologies.",
                'sections' => [
                    ['heading' => 'What cookies are', 'body' => [
                        'Cookies are small text files stored on your device that help websites remember your session and preferences.',
                    ]],
                    ['heading' => 'How we use them', 'body' => [
                        'Essential cookies keep you signed in and protect against cross-site request forgery. We may also use analytics cookies to understand how the platform is used so we can improve it.',
                    ]],
                    ['heading' => 'Managing cookies', 'body' => [
                        'You can clear or block cookies in your browser settings, but disabling essential cookies will prevent you from signing in and completing transactions.',
                    ]],
                ],
            ],
            'refunds' => [
                'title'    => 'Refund Policy',
                'intro'    => 'When and how buyers can get their money back.',
                'sections' => [
                    ['heading' => 'Escrow protection', 'body' => [
                        'Every payment is held in escrow until delivery is confirmed. If a seller fails to deliver, your funds remain protected and can be refunded.',
                    ]],
                    ['heading' => 'Digital products', 'body' => [
                        'Because digital goods are delivered instantly, refunds are considered where the file is faulty, materially different from its description, or never delivered. Open a support ticket within the protection window to request a review.',
                    ]],
                    ['heading' => 'Services and consultations', 'body' => [
                        'Service orders include a revision process. If a delivery cannot be made acceptable, you may raise a dispute and our team will mediate a fair outcome — release, partial refund or full refund.',
                    ]],
                    ['heading' => 'Physical goods', 'body' => [
                        'Physical orders follow the Returns/RMA process: request a return, ship the item back, and the escrowed amount is refunded once the return is confirmed.',
                    ]],
                    ['heading' => 'How refunds are paid', 'body' => [
                        'Approved refunds are credited to your platform wallet, from which you can withdraw to your bank account.',
                    ]],
                ],
            ],
            'disputes' => [
                'title'    => 'Dispute Policy',
                'intro'    => 'How we resolve disagreements between buyers and sellers.',
                'sections' => [
                    ['heading' => 'When to open a dispute', 'body' => [
                        'If you and the other party cannot resolve an issue directly — non-delivery, an item not as described, poor quality or an unresponsive counterparty — you can raise a dispute from the relevant order or escrow.',
                    ]],
                    ['heading' => 'What happens next', 'body' => [
                        'Opening a dispute freezes the escrowed funds so neither side can move them. Both parties can submit messages and evidence in a shared thread.',
                    ]],
                    ['heading' => 'Resolution', 'body' => [
                        'Our team reviews the evidence and decides a fair outcome: release to the seller, refund to the buyer, or a split. Decisions are final once the escrow is settled.',
                    ]],
                    ['heading' => 'Acting in good faith', 'body' => [
                        'Provide clear, honest evidence. Abuse of the dispute process — fraudulent claims or fabricated evidence — may result in account suspension.',
                    ]],
                ],
            ],
        ];
    }
}
