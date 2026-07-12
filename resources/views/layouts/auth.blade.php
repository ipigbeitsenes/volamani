<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b1220">
    @include('layouts.partials.favicon')
    <title>@yield('title', 'Sign In') — {{ settings('site_name', config('app.name', 'Volamani')) }}</title>
    <meta name="description" content="@yield('meta_description', 'Sign in or create your account to buy and sell on ' . settings('site_name', 'Volamani') . '.')">
    {{-- Account/auth screens carry no unique content — keep them out of the search index. --}}
    <meta name="robots" content="noindex, follow">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --vl-primary: #1a56db;
            --vl-primary-rgb: 26, 86, 219;
            --vl-primary-dark: #1143b0;
            --vl-gradient: linear-gradient(135deg, #1a56db 0%, #4f46e5 100%);
        }
        * { -webkit-font-smoothing: antialiased; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(140deg, #0b1220 0%, #15275f 55%, #1a56db 120%);
            min-height: 100vh; display: flex; align-items: center;
            position: relative; overflow-x: hidden;
        }
        body::before, body::after {
            content: ""; position: absolute; border-radius: 50%; z-index: 0;
        }
        body::before { width: 480px; height: 480px; top: -160px; right: -120px; background: radial-gradient(circle, rgba(245,158,11,.22), transparent 70%); }
        body::after  { width: 520px; height: 520px; bottom: -220px; left: -160px; background: radial-gradient(circle, rgba(79,70,229,.4), transparent 70%); }
        h1,h2,h3,h4,h5 { font-family: 'Plus Jakarta Sans','Inter',sans-serif; letter-spacing: -.02em; }
        .auth-card { border: none; border-radius: 20px; box-shadow: 0 30px 70px rgba(0,0,0,.28); }
        .auth-brand { font-family: 'Plus Jakarta Sans',sans-serif; font-weight: 800; font-size: 1.9rem; }
        .brand-mark {
            width: 46px; height: 46px; border-radius: 13px; background: var(--vl-gradient);
            display: inline-flex; align-items: center; justify-content: center; color: #fff;
            box-shadow: 0 10px 24px -8px rgba(26,86,219,.8);
        }
        .btn { font-weight: 600; border-radius: 10px; padding: .6rem 1.1rem; transition: all .18s; }
        .btn-primary { background: var(--vl-gradient); border: none; box-shadow: 0 8px 18px -8px rgba(var(--vl-primary-rgb),.6); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 22px -8px rgba(var(--vl-primary-rgb),.7); }
        .form-control, .form-select { border-radius: 10px; padding: .65rem .9rem; }
        .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 .2rem rgba(var(--vl-primary-rgb),.15); }
        .form-label { font-weight: 600; font-size: .875rem; color: #475569; }
        .alert { border-radius: 12px; }
    </style>

    @stack('styles')
</head>
<body>
    <div class="container position-relative" style="z-index:1;">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="text-center mb-4">
                    <a href="{{ route('home') }}" class="text-decoration-none d-inline-flex align-items-center gap-2">
                        <span class="brand-mark"><i class="bi bi-send-fill" style="transform:rotate(45deg)"></i></span>
                        <span class="auth-brand text-white">Volamani</span>
                    </a>
                    <p class="text-white-50 mt-2 mb-0">{{ settings('site_tagline', 'Your Digital Business Ecosystem') }}</p>
                </div>

                <div class="card auth-card">
                    <div class="card-body p-4 p-md-5">
                        @if(session('success'))
                            <div class="alert alert-success d-flex align-items-center"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
                        @endif

                        @yield('content')
                    </div>
                </div>

                <div class="text-center mt-3 text-white-50 small">
                    <i class="bi bi-shield-lock me-1"></i>Secured &amp; escrow protected &middot; &copy; {{ date('Y') }} Volamani
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
