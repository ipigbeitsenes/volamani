{{--
    Central SEO / social-share meta block. Included once inside <head>.

    Per-page overrides (set in any child view):
      @section('title', 'Page title')                — page name (site name is appended automatically)
      @section('meta_description', '...')            — 1–2 sentence summary (≤160 chars ideal)
      @section('meta_keywords', 'a, b, c')           — optional
      @section('canonical', url('/exact/url'))       — defaults to the current URL (no query string)
      @section('robots', 'noindex, nofollow')        — defaults to "index, follow"
      @section('og_image', $absoluteImageUrl)        — defaults to the site logo / brand cover
      @section('og_type', 'product')                 — defaults to "website"
      @push('schema')  <script type="application/ld+json">…</script>  @endpush  — extra structured data
--}}
@php
    $seoSite     = settings('site_name', config('app.name', 'Volamani'));
    $seoTagline  = settings('site_tagline', "Africa's Digital Business Ecosystem");
    $seoDefaultDesc = 'Buy and sell digital products, freelance services and physical goods on '
        . $seoSite . ' — Africa\'s trusted multi-vendor marketplace with secure escrow, buyer protection and instant payouts.';

    $seoPageTitle = trim($__env->yieldContent('title'));
    $seoFullTitle = $seoPageTitle !== ''
        ? $seoPageTitle . ' | ' . $seoSite
        : $seoSite . ' — ' . $seoTagline;

    $seoDesc      = trim($__env->yieldContent('meta_description', $seoDefaultDesc));
    $seoKeywords  = trim($__env->yieldContent('meta_keywords',
        'Volamani, African marketplace, digital products, freelance services, online marketplace Nigeria, '
        . 'buy sell online Africa, escrow payment, multi-vendor, sell digital downloads, hire freelancers Africa'));
    $seoCanonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    $seoRobots    = trim($__env->yieldContent('robots', 'index, follow')) . ', max-image-preview:large';
    $seoType      = trim($__env->yieldContent('og_type', 'website'));

    // Resolve OG image → must be an ABSOLUTE url for crawlers.
    $seoImage = trim($__env->yieldContent('og_image'));
    if ($seoImage === '') {
        $seoLogoPath = settings('site_logo');
        $seoImage = $seoLogoPath ? media_url($seoLogoPath) : asset('images/og-cover.svg');
    }
    if ($seoImage && ! \Illuminate\Support\Str::startsWith($seoImage, ['http://', 'https://', '//', 'data:'])) {
        $seoImage = url($seoImage);
    }

    // sameAs social profiles (only include the ones that are configured).
    $seoSocials = array_values(array_filter([
        settings('social_twitter'),
        settings('social_facebook'),
        settings('social_instagram'),
        settings('social_linkedin'),
    ]));
@endphp

<title>{{ $seoFullTitle }}</title>
<meta name="description" content="{{ $seoDesc }}">
<meta name="keywords" content="{{ $seoKeywords }}">
<meta name="robots" content="{{ $seoRobots }}">
<meta name="author" content="{{ $seoSite }}">
<link rel="canonical" href="{{ $seoCanonical }}">

{{-- Open Graph (Facebook, LinkedIn, WhatsApp, Slack…) --}}
<meta property="og:site_name" content="{{ $seoSite }}">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $seoFullTitle }}">
<meta property="og:description" content="{{ $seoDesc }}">
<meta property="og:url" content="{{ $seoCanonical }}">
<meta property="og:locale" content="en_NG">
@if($seoImage)
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:alt" content="{{ $seoSite }} — {{ $seoTagline }}">
@endif

{{-- Twitter / X card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoFullTitle }}">
<meta name="twitter:description" content="{{ $seoDesc }}">
@if($seoImage)<meta name="twitter:image" content="{{ $seoImage }}">@endif
@php $seoTw = settings('social_twitter'); @endphp
@if($seoTw && \Illuminate\Support\Str::contains($seoTw, '/'))
<meta name="twitter:site" content="@{{ \Illuminate\Support\Str::afterLast(rtrim($seoTw, '/'), '/') }}">
@endif

{{-- Sitewide structured data: who Volamani is + a search box in Google results. --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type' => 'Organization',
            '@id'   => url('/') . '#organization',
            'name'  => $seoSite,
            'url'   => url('/'),
            'slogan'=> $seoTagline,
            'logo'  => $seoImage,
            'description' => $seoDefaultDesc,
            'sameAs' => $seoSocials,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'email' => settings('support_email', 'support@volamani.com'),
                'telephone' => settings('support_phone'),
                'availableLanguage' => ['English'],
            ],
        ],
        [
            '@type' => 'WebSite',
            '@id'   => url('/') . '#website',
            'url'   => url('/'),
            'name'  => $seoSite,
            'description' => $seoDefaultDesc,
            'publisher' => ['@id' => url('/') . '#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('marketplace.products.index') . '?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

@stack('schema')
