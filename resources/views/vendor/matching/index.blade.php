@extends('layouts.vendor')

@section('title', 'Leads & Matching')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Leads &amp; Matching</h4>
            <p class="text-muted mb-0">Briefs from buyers looking for what you offer.</p>
        </div>
        <a href="{{ route('vendor.matching.profile') }}" class="btn btn-outline-primary"><i class="bi bi-sliders me-1"></i>Matching profile</a>
    </div>

    @if(! $profile)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-4">
                <i class="bi bi-diagram-3 display-6 text-primary"></i>
                <h6 class="fw-bold mt-2">Set up your matching profile to receive leads</h6>
                <p class="text-muted">Tell us your categories, skills and budget range so we can match you with the right buyers.</p>
                <a href="{{ route('vendor.matching.profile') }}" class="btn btn-primary">Create matching profile</a>
            </div>
        </div>
    @elseif(! $profile->is_accepting)
        <div class="alert alert-warning">Your matching profile is currently <strong>not accepting leads</strong>. <a href="{{ route('vendor.matching.profile') }}">Update it</a> to start receiving matches.</div>
    @endif

    <div class="row g-3 mb-4">
        @php $cards = [['Total leads', $stats['leads'], 'primary'], ['Pending', $stats['pending'], 'warning'], ['Connected', $stats['connected'], 'success']]; @endphp
        @foreach($cards as [$label, $value, $color])
            <div class="col-md-4">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <p class="text-muted small mb-1">{{ $label }}</p>
                    <h4 class="fw-bold mb-0 text-{{ $color }}">{{ $value }}</h4>
                </div></div>
            </div>
        @endforeach
    </div>

    @forelse($leads as $match)
        @php $brief = $match->matchRequest; @endphp
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold mb-0">{{ $brief->title }}</h6>
                            <span class="badge bg-{{ $match->status->badge() }}">{{ $match->status->label() }}</span>
                            <span class="badge bg-{{ $match->scoreColor() }}">{{ $match->score }}% match</span>
                        </div>
                        <p class="text-muted small mb-1 mt-1">{{ \Illuminate\Support\Str::limit($brief->description, 160) }}</p>
                        <div class="small text-muted">
                            @if($brief->category){{ $brief->category }} · @endif
                            {{ $brief->budgetLabel() }}
                            @if($brief->timeline)· {{ $brief->timeline }}@endif
                            @if($brief->remote_ok)· Remote OK @endif
                        </div>
                        @if($brief->skills)
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                @foreach($brief->skills as $skill)<span class="badge bg-light text-dark border">{{ $skill }}</span>@endforeach
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4 text-md-end">
                        @if($match->isConnected())
                            <span class="badge bg-success mb-2 d-block">Connected with {{ $brief->user->name }}</span>
                            <a href="mailto:{{ $brief->user->email }}" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-envelope me-1"></i>{{ $brief->user->email }}</a>
                        @elseif($match->vendor_interested)
                            <span class="small text-muted">You're interested — waiting on the buyer.</span>
                        @else
                            @if($match->requester_interested)<div class="small text-success mb-1"><i class="bi bi-check-circle"></i> Buyer is interested</div>@endif
                            <form action="{{ route('vendor.matching.respond', $match) }}" method="POST" class="d-inline">@csrf
                                <input type="hidden" name="decision" value="interested">
                                <button class="btn btn-sm btn-primary">Interested</button>
                            </form>
                            <form action="{{ route('vendor.matching.respond', $match) }}" method="POST" class="d-inline">@csrf
                                <input type="hidden" name="decision" value="pass">
                                <button class="btn btn-sm btn-link text-muted">Decline</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm"><div class="card-body text-center py-5 text-muted">No leads yet. Keep your matching profile up to date to attract more.</div></div>
    @endforelse

    <div class="mt-3">{{ $leads->withQueryString()->links() }}</div>
</div>
@endsection
