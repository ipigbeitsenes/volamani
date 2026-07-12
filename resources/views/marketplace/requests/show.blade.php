@extends('layouts.app')

@section('title', $productRequest->title . ' — Volamani')

@section('content')
<div class="container py-4">
    <div class="row g-4">

        {{-- Left: Request Details --}}
        <div class="col-lg-7">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('marketplace.requests.index') }}">Requests</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($productRequest->title, 40) }}</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge bg-{{ $productRequest->status->badge() }}">{{ $productRequest->status->label() }}</span>
                        @if($productRequest->category)
                            <span class="badge bg-light text-dark border">{{ $productRequest->category->name }}</span>
                        @endif
                        @if($productRequest->isExpired())
                            <span class="badge bg-warning text-dark">Deadline Passed</span>
                        @endif
                    </div>

                    <h4 class="fw-bold mb-3">{{ $productRequest->title }}</h4>

                    <div class="row g-3 mb-4 text-center">
                        <div class="col-4">
                            <div class="fw-bold text-primary">{{ $productRequest->quotations_count }}</div>
                            <div class="text-muted small">Quotations</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold">{{ $productRequest->budgetRange() }}</div>
                            <div class="text-muted small">Budget</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold">
                                {{ $productRequest->deadline_at ? $productRequest->deadline_at->format('M j') : 'Flexible' }}
                            </div>
                            <div class="text-muted small">Deadline</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-semibold mb-2">Requirements</h6>
                        <div class="text-secondary lh-lg" style="white-space: pre-line;">{{ $productRequest->description }}</div>
                    </div>

                    @if($productRequest->attachments && count($productRequest->attachments) > 0)
                        <div class="mb-3">
                            <h6 class="fw-semibold mb-2">Attachments</h6>
                            @foreach($productRequest->attachments as $attachment)
                                <a href="{{ asset('storage/' . $attachment) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-secondary me-2 mb-2">
                                    <i class="bi bi-download me-1"></i>{{ basename($attachment) }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <img src="{{ $productRequest->buyer->getAvatarUrlAttribute() ?? 'https://ui-avatars.com/api/?name=' . urlencode($productRequest->buyer->name) }}"
                             class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                        <div class="text-muted small">
                            Posted by <strong>{{ $productRequest->buyer->name }}</strong>
                            &middot; {{ $productRequest->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Buyer: Close Request --}}
            @if($isBuyer && $productRequest->isOpen())
                <div class="mb-4">
                    <form action="{{ route('requests.close', $productRequest->id) }}" method="POST"
                          onsubmit="return confirm('Close this request? All pending quotations will be rejected.')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-x-circle me-1"></i> Close Request
                        </button>
                    </form>
                </div>
            @endif

            {{-- Buyer: View All Quotations --}}
            @if($isBuyer)
                <h5 class="fw-bold mb-3">
                    Quotations Received
                    <span class="badge bg-primary ms-2">{{ $productRequest->quotations_count }}</span>
                </h5>

                @if($productRequest->quotations->isEmpty())
                    <div class="card border-0 shadow-sm text-center py-4">
                        <p class="text-muted mb-0">No quotations yet. Share your request to get more visibility.</p>
                    </div>
                @else
                    @foreach($productRequest->quotations->sortBy(fn($q) => $q->status->value !== 'accepted' ? 1 : 0) as $quotation)
                        <div class="card border-0 shadow-sm mb-3 {{ $quotation->isAccepted() ? 'border-success border' : '' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $quotation->vendor->logo_url }}"
                                             class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                        <div>
                                            <a href="{{ route('storefront.show', $quotation->vendor->user->username) }}"
                                               class="fw-semibold text-dark text-decoration-none small">
                                                {{ $quotation->vendor->business_name }}
                                            </a>
                                            <div class="text-muted" style="font-size:0.75rem;">
                                                {{ $quotation->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ $quotation->status->badge() }}">{{ $quotation->status->label() }}</span>
                                </div>

                                <div class="row g-2 mb-2 small">
                                    <div class="col-auto">
                                        <span class="fw-bold text-primary fs-5">{{ money($quotation->price) }}</span>
                                    </div>
                                    <div class="col-auto text-muted pt-1">
                                        <i class="bi bi-clock me-1"></i>{{ $quotation->delivery_days }} days delivery
                                    </div>
                                </div>

                                <p class="small text-secondary mb-2">{{ Str::limit($quotation->message, 200) }}</p>

                                @if($quotation->attachments && count($quotation->attachments) > 0)
                                    <div class="mb-2">
                                        @foreach($quotation->attachments as $att)
                                            <a href="{{ asset('storage/' . $att) }}" target="_blank"
                                               class="btn btn-sm btn-outline-secondary me-1 mb-1">
                                                <i class="bi bi-paperclip me-1"></i>{{ basename($att) }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                @if($quotation->isPending() && $productRequest->isOpen())
                                    <form action="{{ route('requests.accept-quotation', [$productRequest->id, $quotation->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Accept this quotation? Other bids will be declined.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-lg me-1"></i> Accept This Quotation
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif

            @elseif($vendorRecord)
                {{-- Vendor View: Show own quotation or submit form --}}
                @if($hasQuoted && $myQuotation)
                    <div class="card border-0 shadow-sm {{ $myQuotation->isAccepted() ? 'border-success border' : '' }}">
                        <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                            Your Quotation
                            <span class="badge bg-{{ $myQuotation->status->badge() }}">{{ $myQuotation->status->label() }}</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-2 mb-2 small">
                                <div class="col-auto">
                                    <span class="fw-bold text-primary fs-5">{{ money($myQuotation->price) }}</span>
                                </div>
                                <div class="col-auto text-muted pt-1">
                                    <i class="bi bi-clock me-1"></i>{{ $myQuotation->delivery_days }} days delivery
                                </div>
                            </div>
                            <p class="small text-secondary mb-3">{{ $myQuotation->message }}</p>

                            @if($myQuotation->canBeWithdrawn())
                                <form action="{{ route('vendor.quotations.withdraw', $myQuotation->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Withdraw this quotation?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        Withdraw Quotation
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @elseif($productRequest->isOpen())
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white fw-semibold">Submit Your Quotation</div>
                        <div class="card-body">
                            <form action="{{ route('vendor.quotations.store', $productRequest->id) }}"
                                  method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Your Price ({{ currency_symbol() }}) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ currency_symbol() }}</span>
                                            <input type="number" name="price" step="0.01" min="500"
                                                class="form-control @error('price') is-invalid @enderror"
                                                value="{{ old('price') }}" required>
                                        </div>
                                        @error('price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Delivery Days <span class="text-danger">*</span></label>
                                        <input type="number" name="delivery_days" min="1" max="365"
                                            class="form-control @error('delivery_days') is-invalid @enderror"
                                            value="{{ old('delivery_days', 3) }}" required>
                                        @error('delivery_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Your Proposal <span class="text-danger">*</span></label>
                                    <textarea name="message" rows="5"
                                        class="form-control @error('message') is-invalid @enderror"
                                        placeholder="Explain your approach, relevant experience, what's included in your price..."
                                        required>{{ old('message') }}</textarea>
                                    @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Attachments <small class="text-muted">(optional, max 3)</small></label>
                                    <input type="file" name="attachments[]" multiple class="form-control form-control-sm">
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i> Submit Quotation
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-secondary">
                        This request is no longer accepting quotations.
                    </div>
                @endif
            @else
                @guest
                    <div class="card border-0 shadow-sm text-center py-4">
                        <div class="card-body">
                            <p class="text-muted">Log in as a vendor to submit a quotation.</p>
                            <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                        </div>
                    </div>
                @endguest
            @endif
        </div>

        {{-- Right: Summary Sidebar --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm sticky-top" style="top: 1rem;">
                <div class="card-header bg-white fw-semibold">Request Summary</div>
                <div class="card-body">
                    <dl class="row small mb-0">
                        <dt class="col-5 text-muted">Status</dt>
                        <dd class="col-7">
                            <span class="badge bg-{{ $productRequest->status->badge() }}">{{ $productRequest->status->label() }}</span>
                        </dd>

                        <dt class="col-5 text-muted">Budget</dt>
                        <dd class="col-7 fw-semibold">{{ $productRequest->budgetRange() }}</dd>

                        @if($productRequest->deadline_at)
                            <dt class="col-5 text-muted">Deadline</dt>
                            <dd class="col-7 {{ $productRequest->isExpired() ? 'text-danger fw-semibold' : '' }}">
                                {{ $productRequest->deadline_at->format('M j, Y') }}
                            </dd>
                        @endif

                        @if($productRequest->location)
                            <dt class="col-5 text-muted">Location</dt>
                            <dd class="col-7">{{ $productRequest->location }}</dd>
                        @endif

                        @if($productRequest->category)
                            <dt class="col-5 text-muted">Category</dt>
                            <dd class="col-7">{{ $productRequest->category->name }}</dd>
                        @endif

                        <dt class="col-5 text-muted">Posted</dt>
                        <dd class="col-7">{{ $productRequest->created_at->format('M j, Y') }}</dd>

                        <dt class="col-5 text-muted">Quotations</dt>
                        <dd class="col-7 fw-bold text-primary">{{ $productRequest->quotations_count }}</dd>
                    </dl>
                </div>
            </div>

            @if($productRequest->status->value === 'accepted' && $productRequest->acceptedQuotation)
                <div class="card border-0 shadow-sm mt-3 border-success border">
                    <div class="card-header bg-success bg-opacity-10 fw-semibold text-success">
                        <i class="bi bi-check-circle me-2"></i>Accepted Vendor
                    </div>
                    <div class="card-body">
                        <a href="{{ route('storefront.show', $productRequest->acceptedQuotation->vendor->user->username) }}"
                           class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                            <img src="{{ $productRequest->acceptedQuotation->vendor->logo_url }}"
                                 class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">
                            <div>
                                <div class="fw-semibold">{{ $productRequest->acceptedQuotation->vendor->business_name }}</div>
                                <div class="text-success small">
                                    {{ money($productRequest->acceptedQuotation->price) }}
                                    &middot; {{ $productRequest->acceptedQuotation->delivery_days }} days
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
