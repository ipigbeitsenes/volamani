@extends('layouts.vendor')

@section('title', $client->name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <span class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center fw-bold" style="width:54px;height:54px;">{{ $client->initials() }}</span>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0">{{ $client->name }}</h4>
                    <span class="badge bg-{{ $client->status->badge() }}">{{ $client->status->label() }}</span>
                    @if($client->isRegistered())<span class="badge bg-light text-dark border"><i class="bi bi-person-check me-1"></i>Account</span>@endif
                </div>
                <div class="small text-muted">{{ $client->company }}@if($client->company && $client->email) · @endif{{ $client->email }}</div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendor.clients.edit', $client) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
            <form action="{{ route('vendor.clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Remove this client?');">@csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        {{-- Left: details + business --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Details</h6>
                    <dl class="row small mb-0">
                        <dt class="col-5 text-muted">Phone</dt><dd class="col-7">{{ $client->phone ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Source</dt><dd class="col-7"><i class="bi {{ $client->source->icon() }} me-1"></i>{{ $client->source->label() }}</dd>
                        <dt class="col-5 text-muted">Lifetime value</dt><dd class="col-7 fw-semibold">{{ money($client->total_spent) }}</dd>
                        <dt class="col-5 text-muted">Orders</dt><dd class="col-7">{{ $client->orders_count }}</dd>
                        @if($client->address)<dt class="col-5 text-muted">Address</dt><dd class="col-7">{{ $client->address }}</dd>@endif
                    </dl>
                    @if($client->tags)
                        <div class="d-flex flex-wrap gap-1 mt-3">
                            @foreach($client->tags as $tag)<span class="badge bg-light text-dark border">{{ $tag }}</span>@endforeach
                        </div>
                    @endif
                    @if($client->about)<p class="small text-muted mt-3 mb-0">{{ $client->about }}</p>@endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="fw-bold mb-0">Recent business</h6></div>
                <div class="card-body small">
                    @php $any = $business['orders']->isNotEmpty() || $business['serviceOrders']->isNotEmpty() || $business['invoices']->isNotEmpty(); @endphp
                    @forelse($business['orders'] as $o)
                        <div class="d-flex justify-content-between border-bottom py-1"><span><i class="bi bi-box-seam me-1"></i>{{ $o->reference }}</span><span>{{ money($o->total_amount) }}</span></div>
                    @empty @endforelse
                    @foreach($business['serviceOrders'] as $so)
                        <div class="d-flex justify-content-between border-bottom py-1"><span><i class="bi bi-briefcase me-1"></i>{{ $so->reference }}</span><span>{{ money($so->total_amount) }}</span></div>
                    @endforeach
                    @foreach($business['invoices'] as $inv)
                        <div class="d-flex justify-content-between border-bottom py-1"><span><i class="bi bi-receipt me-1"></i>{{ $inv->number }}</span><span>{{ money($inv->total) }}</span></div>
                    @endforeach
                    @unless($any)<p class="text-muted mb-0">No linked transactions.</p>@endunless
                </div>
            </div>
        </div>

        {{-- Right: log + timeline --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Log an interaction</h6>
                    <form action="{{ route('vendor.clients.interactions', $client) }}" method="POST">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select name="type" class="form-select form-select-sm">
                                    @foreach(\App\Enums\InteractionType::cases() as $t)
                                        <option value="{{ $t->value }}">{{ $t->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="title" class="form-control form-control-sm" placeholder="Title (optional)">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="due_at" class="form-control form-control-sm" title="Due date (for tasks)">
                            </div>
                            <div class="col-12">
                                <textarea name="body" class="form-control form-control-sm" rows="2" placeholder="What happened?" required></textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="pinned" value="1" id="pinned">
                                    <label class="form-check-label small" for="pinned">Pin to top</label>
                                </div>
                                <button class="btn btn-sm btn-primary">Add</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="fw-bold mb-0">Timeline</h6></div>
                <div class="card-body">
                    @forelse($client->interactions as $item)
                        <div class="d-flex gap-3 pb-3 mb-3 border-bottom">
                            <div class="text-{{ $item->type->color() }}"><i class="bi {{ $item->type->icon() }} fs-5"></i></div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span class="fw-semibold">{{ $item->title ?: $item->type->label() }}</span>
                                        @if($item->pinned)<i class="bi bi-pin-angle-fill text-warning ms-1" title="Pinned"></i>@endif
                                        @if($item->isOverdue())<span class="badge bg-danger ms-1">Overdue</span>@endif
                                        @if($item->isTask() && $item->isComplete())<span class="badge bg-success ms-1">Done</span>@endif
                                    </div>
                                    <span class="small text-muted">{{ $item->occurred_at?->diffForHumans() ?? $item->created_at->diffForHumans() }}</span>
                                </div>
                                @if($item->body)<p class="mb-1 small">{{ $item->body }}</p>@endif
                                <div class="small text-muted">
                                    {{ $item->author->name ?? 'You' }}
                                    @if($item->isTask() && $item->due_at)· due {{ $item->due_at->format('d M Y') }}@endif
                                    @if($item->isTask())
                                        · <form action="{{ route('vendor.clients.interactions.complete', [$client, $item]) }}" method="POST" class="d-inline">@csrf
                                            <button class="btn btn-link btn-sm p-0 align-baseline">{{ $item->isComplete() ? 'Reopen' : 'Mark done' }}</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0">No interactions logged yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
