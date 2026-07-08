@extends('layouts.account')

@section('title', $request->title)

@section('content')
<div class="container py-4" style="max-width: 860px;">
    <a href="{{ route('matching.index') }}" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left"></i> All briefs</a>

    <div class="d-flex justify-content-between align-items-start mt-2 mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0">{{ $request->title }}</h4>
                <span class="badge bg-{{ $request->status->badge() }}">{{ $request->status->label() }}</span>
            </div>
            <div class="small text-muted mt-1">
                <span><i class="bi {{ $request->looking_for->icon() }} me-1"></i>{{ $request->looking_for->label() }}</span>
                @if($request->category)· <span>{{ $request->category }}</span>@endif
                · <span>{{ $request->budgetLabel() }}</span>
                @if($request->timeline)· <span>{{ $request->timeline }}</span>@endif
            </div>
        </div>
        @if($request->isOpen())
            <form action="{{ route('matching.close', $request) }}" method="POST" onsubmit="return confirm('Close this brief?');">@csrf
                <button class="btn btn-sm btn-outline-secondary">Close brief</button>
            </form>
        @endif
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <p class="mb-2">{{ $request->description }}</p>
            @if($request->skills)
                <div class="d-flex flex-wrap gap-1">
                    @foreach($request->skills as $skill)<span class="badge bg-light text-dark border">{{ $skill }}</span>@endforeach
                </div>
            @endif
        </div>
    </div>

    <h6 class="fw-bold mb-3">Matched vendors ({{ $request->matches->count() }})</h6>

    @forelse($request->matches as $match)
        @php $vendor = $match->vendor; @endphp
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-7">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold mb-0">{{ $vendor->business_name }}</h6>
                            @if($vendor->isVerified())<i class="bi bi-patch-check-fill text-success" title="Verified"></i>@endif
                            <span class="badge bg-{{ $match->status->badge() }}">{{ $match->status->label() }}</span>
                        </div>
                        @if($vendor->matchingProfile?->headline)<div class="small text-muted">{{ $vendor->matchingProfile->headline }}</div>@endif
                        <div class="small text-muted mt-1">
                            @if($vendor->category){{ $vendor->category }} · @endif
                            Trust {{ $vendor->trust_score }}/100
                            @if($vendor->city)· {{ $vendor->city }}@endif
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="fw-bold fs-4 text-{{ $match->scoreColor() }}">{{ $match->score }}%</div>
                        <div class="progress" style="height:5px;"><div class="progress-bar bg-{{ $match->scoreColor() }}" style="width: {{ $match->score }}%"></div></div>
                        <div class="small text-muted mt-1">match</div>
                    </div>
                    <div class="col-md-3 text-md-end">
                        @if($match->isConnected())
                            <span class="badge bg-success mb-2 d-block">Connected!</span>
                            <a href="{{ $vendor->storefront_url }}" class="btn btn-sm btn-outline-primary w-100 mb-1" target="_blank">View storefront</a>
                            @if($vendor->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/', '', $vendor->whatsapp) }}" class="btn btn-sm btn-success w-100" target="_blank"><i class="bi bi-whatsapp me-1"></i>Contact</a>@endif
                        @elseif($match->isDeclined())
                            <span class="text-muted small">Dismissed</span>
                        @elseif($request->isOpen())
                            @if($match->vendor_interested)<div class="small text-success mb-1"><i class="bi bi-check-circle"></i> Vendor is interested</div>@endif
                            @unless($match->requester_interested)
                                <form action="{{ route('matching.respond', [$request, $match]) }}" method="POST" class="d-inline">@csrf
                                    <input type="hidden" name="decision" value="interested">
                                    <button class="btn btn-sm btn-primary">I'm interested</button>
                                </form>
                                <form action="{{ route('matching.respond', [$request, $match]) }}" method="POST" class="d-inline">@csrf
                                    <input type="hidden" name="decision" value="pass">
                                    <button class="btn btn-sm btn-link text-muted">Pass</button>
                                </form>
                            @else
                                <span class="small text-muted">Waiting on vendor…</span>
                            @endunless
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                No vendors matched yet. We'll keep looking as vendors update their profiles.
            </div>
        </div>
    @endforelse
</div>
@endsection
