<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — Volamani</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f8fafc; color: #1e293b; }
        .vl-code { font-size: clamp(4rem, 14vw, 8rem); font-weight: 800; line-height: 1; color: #1a56db; }
        .btn-primary { background:#1a56db; border-color:#1a56db; }
        .btn-primary:hover { background:#1143b0; border-color:#1143b0; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="text-center px-3">
        <div class="vl-code">@yield('code')</div>
        <h1 class="h4 fw-bold mb-2">@yield('title')</h1>
        <p class="text-muted mb-4 mx-auto" style="max-width:480px;">@yield('message')</p>
        <a href="{{ url('/') }}" class="btn btn-primary px-4"><i class="bi bi-house-door me-1"></i>Back to home</a>
    </div>
</body>
</html>
