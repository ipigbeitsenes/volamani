@extends('layouts.app')

@section('title', 'Contact Us')
@section('meta_description', 'Get in touch with the Volamani team — support, sales and partnerships.')

@section('content')

{{-- ── Header ── --}}
<section class="position-relative overflow-hidden text-white py-5" style="background: var(--vl-gradient-dark);">
    <div class="container py-3 position-relative text-center" style="max-width: 680px;">
        <span class="eyebrow text-warning"><i class="bi bi-envelope-paper"></i> Contact</span>
        <h1 class="fw-bold text-white mt-2 mb-3">Get in touch</h1>
        <p class="mb-0" style="color:rgba(255,255,255,.74);">We usually respond within 1 business day.</p>
    </div>
</section>

<section class="section bg-white">
    <div class="container">
        <div class="row g-5 justify-content-center">
            {{-- Contact details --}}
            <div class="col-lg-4">
                <h2 class="h4 fw-bold mb-4">Reach us</h2>
                <div class="d-flex flex-column gap-4">
                    <div class="d-flex gap-3">
                        <div class="feature-tile sm bg-primary bg-opacity-10 text-primary flex-shrink-0"><i class="bi bi-envelope"></i></div>
                        <div>
                            <div class="fw-semibold">Email</div>
                            <a href="mailto:{{ $contactEmail }}" class="text-muted small text-decoration-none">{{ $contactEmail }}</a>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="feature-tile sm bg-success bg-opacity-10 text-success flex-shrink-0"><i class="bi bi-whatsapp"></i></div>
                        <div>
                            <div class="fw-semibold">Phone / WhatsApp</div>
                            <span class="text-muted small">{{ $contactPhone }}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="feature-tile sm bg-warning bg-opacity-10 text-warning flex-shrink-0"><i class="bi bi-life-preserver"></i></div>
                        <div>
                            <div class="fw-semibold">Help Center</div>
                            <a href="{{ route('pages.help') }}" class="text-muted small text-decoration-none">Browse answers &amp; FAQs</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact form --}}
            <div class="col-lg-6">
                <div class="card shadow-sm p-4 p-md-5">
                    <h2 class="h4 fw-bold mb-4">Send us a message</h2>
                    <form method="POST" action="{{ route('pages.contact.submit') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Your name</label>
                                <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}"
                                       class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email address</label>
                                <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}"
                                       class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="subject">Subject</label>
                                <input type="text" id="subject" name="subject" value="{{ old('subject') }}"
                                       class="form-control @error('subject') is-invalid @enderror" required>
                                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="message">Message</label>
                                <textarea id="message" name="message" rows="5"
                                          class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-send me-2"></i>Send message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
