<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Services\Affiliate\AffiliateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferralLinkController extends Controller
{
    public function __construct(private AffiliateService $affiliateService) {}

    /**
     * Public entry point for a share link (/r/{code}). Logs the click, drops an
     * attribution cookie, then forwards to the homepage (or the registration
     * form, so the code rides along into signup).
     */
    public function track(Request $request, string $code): RedirectResponse
    {
        $this->affiliateService->trackClick($code, $request);

        $days = (int) settings('affiliate_cookie_days', 30);
        $redirect = redirect()->route('register', ['ref' => $code]);

        // Remember the referrer across the visit even if they register later.
        return $redirect->withCookie(cookie('vlm_ref', $code, $days * 24 * 60));
    }
}
