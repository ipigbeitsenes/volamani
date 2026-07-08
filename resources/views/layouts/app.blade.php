<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1a56db">
    @include('layouts.partials.favicon')
    <title>@yield('title', 'Volamani') — Africa's Digital Business Ecosystem</title>
    <meta name="description" content="@yield('meta_description', 'Buy and sell digital products, freelance services, and grow your African business on Volamani.')">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --vl-primary: #1a56db;
            --vl-primary-rgb: 26, 86, 219;
            --vl-primary-dark: #1143b0;
            --vl-primary-light: #3b82f6;
            --vl-indigo: #4f46e5;
            --vl-accent: #f59e0b;
            --vl-accent-dark: #d97706;
            --vl-success: #059669;
            --vl-danger: #dc2626;
            --vl-ink: #0f172a;
            --vl-body: #334155;
            --vl-muted: #64748b;
            --vl-surface: #f5f7fc;
            --vl-card: #ffffff;
            --vl-border: #e8ecf4;
            --vl-radius: 16px;
            --vl-radius-sm: 12px;
            --vl-shadow-sm: 0 1px 2px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.05);
            --vl-shadow: 0 4px 6px -1px rgba(15,23,42,.05), 0 2px 4px -2px rgba(15,23,42,.05);
            --vl-shadow-md: 0 12px 28px -8px rgba(15,23,42,.10), 0 6px 12px -6px rgba(15,23,42,.06);
            --vl-shadow-lg: 0 24px 50px -12px rgba(15,23,42,.20);
            --vl-gradient: linear-gradient(135deg, #1a56db 0%, #4f46e5 100%);
            --vl-gradient-soft: linear-gradient(135deg, rgba(26,86,219,.08) 0%, rgba(79,70,229,.08) 100%);
            --vl-gradient-dark: linear-gradient(140deg, #0b1220 0%, #15275f 55%, #1a56db 120%);
            --bs-body-color: #334155;
            --bs-border-color: #e8ecf4;
        }

        * { -webkit-font-smoothing: antialiased; -moz-osx-osmoothing: grayscale; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: var(--vl-surface);
            color: var(--vl-body);
            letter-spacing: -0.01em;
        }

        h1, h2, h3, h4, h5, h6, .navbar-brand, .display-1, .display-2, .display-3, .display-4, .display-5, .display-6 {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            letter-spacing: -0.025em;
            color: var(--vl-ink);
        }

        /* ── Typographic helpers ─────────────────────────────────── */
        .text-primary { color: var(--vl-primary) !important; }
        .text-muted { color: var(--vl-muted) !important; }
        .text-gradient {
            background: var(--vl-gradient);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .eyebrow {
            display: inline-flex; align-items: center; gap: .4rem;
            font-size: .75rem; font-weight: 700; letter-spacing: .08em;
            text-transform: uppercase; color: var(--vl-primary);
        }
        .lead-muted { color: var(--vl-muted); font-size: 1.075rem; line-height: 1.7; }

        /* ── Buttons ─────────────────────────────────────────────── */
        .btn { font-weight: 600; border-radius: 10px; padding: .55rem 1.1rem; transition: all .18s ease; }
        .btn-lg { padding: .8rem 1.6rem; border-radius: 12px; }
        .btn-sm { border-radius: 8px; }
        .btn-primary {
            background: var(--vl-gradient); border: none;
            box-shadow: 0 6px 16px -6px rgba(var(--vl-primary-rgb), .55);
        }
        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(135deg, #1143b0 0%, #4338ca 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 22px -8px rgba(var(--vl-primary-rgb), .65);
        }
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            border: none; color: #422006;
            box-shadow: 0 6px 16px -6px rgba(245,158,11,.55);
        }
        .btn-warning:hover { transform: translateY(-2px); box-shadow: 0 12px 22px -8px rgba(245,158,11,.6); color: #422006; }
        .btn-outline-primary { border-color: var(--vl-primary); color: var(--vl-primary); }
        .btn-outline-primary:hover { background: var(--vl-primary); border-color: var(--vl-primary); transform: translateY(-2px); }
        .btn-outline-light:hover { transform: translateY(-2px); }
        .btn-light { background: #fff; border-color: var(--vl-border); }
        .btn-light:hover { background: #fff; border-color: #cbd5e1; transform: translateY(-2px); box-shadow: var(--vl-shadow); }
        .btn-dark { background: var(--vl-ink); border-color: var(--vl-ink); }
        .btn-pill { border-radius: 50rem; }

        /* ── Cards ───────────────────────────────────────────────── */
        .card {
            border: 1px solid var(--vl-border);
            border-radius: var(--vl-radius);
            background: var(--vl-card);
            box-shadow: var(--vl-shadow-sm);
        }
        .card.shadow-sm { box-shadow: var(--vl-shadow) !important; }
        .card.shadow { box-shadow: var(--vl-shadow-md) !important; }
        .hover-lift { transition: transform .22s ease, box-shadow .22s ease; }
        .hover-lift:hover { transform: translateY(-6px); box-shadow: var(--vl-shadow-md) !important; }
        a.card:hover h6, a.card:hover .card-title { color: var(--vl-primary) !important; }

        /* ── Badges ──────────────────────────────────────────────── */
        .badge { font-weight: 600; letter-spacing: .01em; padding: .42em .7em; border-radius: 7px; }
        .rounded-pill.badge, .badge.rounded-pill { border-radius: 50rem; }
        .badge-soft-primary { background: rgba(var(--vl-primary-rgb), .1); color: var(--vl-primary); }
        .badge-soft-success { background: rgba(5,150,105,.12); color: var(--vl-success); }
        .badge-soft-warning { background: rgba(245,158,11,.14); color: var(--vl-accent-dark); }

        /* ── Forms ───────────────────────────────────────────────── */
        .form-control, .form-select {
            border-radius: 10px; border-color: var(--vl-border);
            padding: .6rem .85rem; transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--vl-primary-light);
            box-shadow: 0 0 0 .2rem rgba(var(--vl-primary-rgb), .15);
        }
        .input-group-text { border-radius: 10px; border-color: var(--vl-border); background: #f8fafc; }
        .form-label { font-weight: 600; font-size: .875rem; color: #475569; margin-bottom: .4rem; }

        /* ── Navbar ──────────────────────────────────────────────── */
        .navbar {
            background: rgba(255,255,255,.85) !important;
            backdrop-filter: saturate(180%) blur(14px);
            -webkit-backdrop-filter: saturate(180%) blur(14px);
            border-bottom: 1px solid var(--vl-border) !important;
            box-shadow: 0 1px 0 rgba(15,23,42,.02);
        }
        .navbar-brand { color: var(--vl-primary) !important; font-weight: 800; }
        .navbar .nav-link { font-weight: 500; color: #475569; border-radius: 8px; padding: .45rem .7rem !important; transition: color .15s, background .15s; }
        .navbar .nav-link:hover { color: var(--vl-primary); background: rgba(var(--vl-primary-rgb), .06); }
        .navbar .nav-link.active, .navbar .nav-link.fw-bold { color: var(--vl-primary) !important; }
        .dropdown-menu { border: 1px solid var(--vl-border); border-radius: 14px; box-shadow: var(--vl-shadow-md); padding: .4rem; }
        .dropdown-item { border-radius: 9px; padding: .55rem .75rem; font-size: .9rem; }
        .dropdown-item:hover { background: var(--vl-gradient-soft); color: var(--vl-primary); }
        .dropdown-item.text-danger:hover { background: rgba(220,38,38,.08); color: var(--vl-danger); }

        /* ── Sections & surfaces ─────────────────────────────────── */
        .section { padding-top: 4.5rem; padding-bottom: 4.5rem; }
        .bg-surface { background: var(--vl-surface) !important; }
        .bg-gradient-brand { background: var(--vl-gradient) !important; }
        .bg-gradient-dark { background: var(--vl-gradient-dark) !important; }
        .glass {
            background: rgba(255,255,255,.1);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.18);
        }

        /* ── Decorative icon tiles ───────────────────────────────── */
        .feature-tile {
            width: 56px; height: 56px; border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .feature-tile.sm { width: 44px; height: 44px; border-radius: 12px; font-size: 1.15rem; }

        /* ── Alerts / toasts ─────────────────────────────────────── */
        .vl-toast-stack {
            position: fixed; top: 84px; right: 1rem; z-index: 1080;
            width: 360px; max-width: calc(100vw - 2rem);
            display: flex; flex-direction: column; gap: .6rem;
        }
        .vl-toast-stack .alert {
            border: none; border-radius: 14px; box-shadow: var(--vl-shadow-lg);
            border-left: 4px solid currentColor; backdrop-filter: blur(6px);
            animation: vlSlideIn .35s cubic-bezier(.16,1,.3,1);
        }
        @keyframes vlSlideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: none; } }
        .alert { border-radius: 12px; }

        /* ── Misc ────────────────────────────────────────────────── */
        a { text-decoration: none; }
        .nav-tabs { border-bottom: 2px solid var(--vl-border); }
        .nav-tabs .nav-link { border: none; color: var(--vl-muted); font-weight: 600; border-bottom: 2px solid transparent; margin-bottom: -2px; }
        .nav-tabs .nav-link:hover { color: var(--vl-primary); }
        .nav-tabs .nav-link.active { color: var(--vl-primary); background: none; border-bottom: 2px solid var(--vl-primary); }
        .table { --bs-table-border-color: var(--vl-border); }
        ::selection { background: rgba(var(--vl-primary-rgb), .18); }
        ::-webkit-scrollbar { width: 11px; height: 11px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; border: 3px solid var(--vl-surface); }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    @stack('styles')
</head>
<body>

    @include('layouts.partials.navbar')

    @php
        $vlFlashes = collect([
            ['success', 'success', 'bi-check-circle-fill'],
            ['error',   'danger',  'bi-exclamation-triangle-fill'],
            ['warning', 'warning', 'bi-exclamation-circle-fill'],
            ['info',    'info',    'bi-info-circle-fill'],
        ])->filter(fn ($f) => session($f[0]));
    @endphp
    @if($vlFlashes->isNotEmpty())
        <div class="vl-toast-stack">
            @foreach($vlFlashes as $f)
                <div class="alert alert-{{ $f[1] }} alert-dismissible fade show shadow d-flex align-items-start" role="alert">
                    <i class="bi {{ $f[2] }} me-2 mt-1"></i>
                    <div class="flex-grow-1 small">{{ session($f[0]) }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endforeach
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    @include('layouts.partials.chat-widget')

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss flash toasts after a few seconds.
        document.querySelectorAll('.vl-toast-stack .alert').forEach(function (el, i) {
            setTimeout(function () {
                try { bootstrap.Alert.getOrCreateInstance(el).close(); } catch (e) {}
            }, 5200 + i * 600);
        });
    </script>

    @stack('scripts')
</body>
</html>
