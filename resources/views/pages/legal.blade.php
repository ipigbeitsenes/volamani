@extends('layouts.app')

@section('title', $doc['title'])
@section('meta_description', $doc['intro'])

@section('content')

{{-- ── Page header ── --}}
<section class="position-relative overflow-hidden text-white py-5" style="background: var(--vl-gradient-dark);">
    <div class="container py-3 position-relative">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-3">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page">{{ $doc['title'] }}</li>
            </ol>
        </nav>
        <span class="eyebrow text-warning"><i class="bi bi-file-earmark-text"></i> Legal</span>
        <h1 class="fw-bold text-white mt-2 mb-2">{{ $doc['title'] }}</h1>
        <p class="mb-0" style="color:rgba(255,255,255,.72);max-width:640px;">{{ $doc['intro'] }}</p>
    </div>
</section>

<section class="section bg-white">
    <div class="container" style="max-width: 820px;">
        <p class="text-muted small mb-4"><i class="bi bi-clock-history me-1"></i>Last updated {{ now()->format('F Y') }}</p>

        @foreach($doc['sections'] as $i => $section)
            <div class="mb-4">
                <h2 class="h5 fw-bold mb-2">{{ $i + 1 }}. {{ $section['heading'] }}</h2>
                @foreach($section['body'] as $para)
                    <p class="text-body" style="line-height:1.75;">{{ $para }}</p>
                @endforeach
            </div>
        @endforeach

        <div class="alert alert-light border d-flex align-items-start gap-3 mt-5">
            <i class="bi bi-info-circle-fill text-primary fs-5"></i>
            <div class="small mb-0">
                Still have questions? Visit the <a href="{{ route('pages.help') }}">Help Center</a>
                or <a href="{{ route('pages.contact') }}">contact our team</a>.
            </div>
        </div>

        {{-- Cross-links to the other legal documents --}}
        <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
            @foreach(['privacy' => 'Privacy', 'terms' => 'Terms', 'cookies' => 'Cookies', 'refunds' => 'Refunds', 'disputes' => 'Disputes'] as $key => $label)
                <a href="{{ route('pages.legal', $key) }}"
                   class="btn btn-sm rounded-pill {{ $slug === $key ? 'btn-primary' : 'btn-light border' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>
</section>

@endsection
