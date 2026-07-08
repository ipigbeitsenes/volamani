@extends('layouts.account')

@section('title', 'Order #' . $serviceOrder->reference)

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            @if($isBuyer)
                <a href="{{ route('service-orders.index') }}" class="text-muted text-decoration-none small">
                    <i class="bi bi-arrow-left me-1"></i> My Orders
                </a>
            @else
                <a href="{{ route('vendor.service-orders.index') }}" class="text-muted text-decoration-none small">
                    <i class="bi bi-arrow-left me-1"></i> Received Orders
                </a>
            @endif
            <h4 class="fw-bold mb-0 mt-1">
                {{ Str::limit($serviceOrder->service->title, 60) }}
            </h4>
            <p class="text-muted small mb-0">Order {{ $serviceOrder->reference }}</p>
        </div>
        <div class="text-end">
            <span class="badge bg-{{ $serviceOrder->status->badge() }} fs-6">
                {{ $serviceOrder->status->label() }}
            </span>
            @if($serviceOrder->isOverdue())
                <div><span class="badge bg-danger">Overdue</span></div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- Left: Order Workspace --}}
        <div class="col-lg-8">

            {{-- Status Timeline --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    @php
                        $steps = [
                            ['status' => 'pending', 'label' => 'Order Placed', 'icon' => 'bi-cart-check'],
                            ['status' => 'active', 'label' => 'Payment Confirmed', 'icon' => 'bi-credit-card'],
                            ['status' => 'in_progress', 'label' => 'Requirements Submitted', 'icon' => 'bi-pencil-square'],
                            ['status' => 'delivered', 'label' => 'Work Delivered', 'icon' => 'bi-send-check'],
                            ['status' => 'completed', 'label' => 'Completed', 'icon' => 'bi-check-circle'],
                        ];
                        $statusOrder = ['pending' => 0, 'active' => 1, 'in_progress' => 2, 'delivered' => 3, 'revision_requested' => 3, 'completed' => 4];
                        $currentStep = $statusOrder[$serviceOrder->status->value] ?? 0;
                    @endphp
                    <div class="d-flex justify-content-between align-items-center position-relative">
                        <div class="position-absolute top-50 start-0 end-0 translate-middle-y bg-light" style="height:3px;z-index:0;"></div>
                        @foreach($steps as $i => $step)
                            <div class="text-center position-relative" style="z-index:1;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1 {{ $i <= $currentStep ? 'bg-primary text-white' : 'bg-light text-muted border' }}"
                                     style="width:38px;height:38px;">
                                    <i class="bi {{ $step['icon'] }}"></i>
                                </div>
                                <div class="small {{ $i <= $currentStep ? 'fw-semibold text-primary' : 'text-muted' }}" style="font-size:0.7rem;">
                                    {{ $step['label'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Buyer: Submit Requirements --}}
            @if($isBuyer && $serviceOrder->status->value === 'active')
                <div class="card border-0 shadow-sm mb-4 border-warning">
                    <div class="card-header bg-warning bg-opacity-10 fw-semibold">
                        <i class="bi bi-pencil-square me-2 text-warning"></i>Submit Your Requirements
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Describe exactly what you need. Be as detailed as possible to help the vendor deliver great work.</p>
                        <form action="{{ route('service-orders.requirements', $serviceOrder->id) }}" method="POST">
                            @csrf
                            <textarea name="requirements" rows="6" class="form-control mb-3 @error('requirements') is-invalid @enderror"
                                placeholder="Describe your requirements in detail...">{{ old('requirements') }}</textarea>
                            @error('requirements') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <button type="submit" class="btn btn-warning fw-semibold">
                                <i class="bi bi-send me-1"></i> Submit Requirements
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Vendor: Deliver Order --}}
            @if($isVendor && in_array($serviceOrder->status->value, ['in_progress', 'revision_requested']))
                <div class="card border-0 shadow-sm mb-4 border-primary">
                    <div class="card-header bg-primary bg-opacity-10 fw-semibold">
                        <i class="bi bi-send-check me-2 text-primary"></i>Submit Delivery
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendor.service-orders.deliver', $serviceOrder->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Delivery Message <span class="text-danger">*</span></label>
                                <textarea name="message" rows="5" class="form-control @error('message') is-invalid @enderror"
                                    placeholder="Describe what you've delivered and any instructions for the buyer...">{{ old('message') }}</textarea>
                                @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Attachment <small class="text-muted">(optional, max 50MB)</small></label>
                                <input type="file" name="attachment" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="bi bi-send me-1"></i> Submit Delivery
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Buyer: Delivery Actions --}}
            @if($isBuyer && $serviceOrder->status->value === 'delivered')
                <div class="card border-0 shadow-sm mb-4 border-success">
                    <div class="card-header bg-success bg-opacity-10 fw-semibold">
                        <i class="bi bi-check-circle me-2 text-success"></i>Delivery Received
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Please review the delivery. You can accept it or request a revision
                            ({{ $serviceOrder->remainingRevisions() }} revision(s) remaining).
                        </p>
                        <div class="d-flex gap-2 mb-4">
                            <form action="{{ route('service-orders.complete', $serviceOrder->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success fw-semibold"
                                    onclick="return confirm('Accept this delivery and mark order as complete?')">
                                    <i class="bi bi-check-lg me-1"></i> Accept & Complete
                                </button>
                            </form>
                            @if($serviceOrder->canRequestRevision())
                                <button type="button" class="btn btn-outline-warning fw-semibold"
                                    data-bs-toggle="collapse" data-bs-target="#revisionForm">
                                    <i class="bi bi-arrow-repeat me-1"></i> Request Revision ({{ $serviceOrder->remainingRevisions() }} left)
                                </button>
                            @endif
                        </div>

                        @if($serviceOrder->canRequestRevision())
                            <div class="collapse" id="revisionForm">
                                <form action="{{ route('service-orders.revision', $serviceOrder->id) }}" method="POST">
                                    @csrf
                                    <textarea name="feedback" rows="4" class="form-control mb-2 @error('feedback') is-invalid @enderror"
                                        placeholder="Describe what needs to be revised...">{{ old('feedback') }}</textarea>
                                    @error('feedback') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <button type="submit" class="btn btn-warning btn-sm">Submit Revision Request</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Message Thread --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Messages</div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;" id="messageThread">
                    @foreach($serviceOrder->messages as $msg)
                        @php
                            $isMe = $msg->sender_id === auth()->id();
                        @endphp
                        @if($msg->is_system)
                            <div class="text-center my-3">
                                <span class="badge bg-light text-muted border px-3 py-2">{{ $msg->message }}</span>
                            </div>
                        @else
                            <div class="d-flex gap-2 mb-3 {{ $isMe ? 'flex-row-reverse' : '' }}">
                                <img src="{{ $msg->sender->getAvatarUrlAttribute() ?? 'https://ui-avatars.com/api/?name=' . urlencode($msg->sender->name) }}"
                                     class="rounded-circle flex-shrink-0"
                                     style="width:36px;height:36px;object-fit:cover;">
                                <div style="max-width: 70%;">
                                    @if($msg->is_delivery)
                                        <div class="badge bg-primary mb-1">Delivery</div>
                                    @endif
                                    <div class="p-3 rounded-3 small {{ $isMe ? 'bg-primary text-white' : 'bg-light' }}">
                                        @if($msg->message)
                                            <p class="mb-{{ $msg->attachment ? 2 : 0 }}">{{ $msg->message }}</p>
                                        @endif
                                        @if($msg->attachment)
                                            <a href="{{ $msg->attachment_url }}"
                                               target="_blank"
                                               class="d-flex align-items-center gap-1 {{ $isMe ? 'text-white' : 'text-primary' }} text-decoration-none small">
                                                <i class="bi bi-paperclip"></i>
                                                {{ $msg->attachment_name ?? 'Attachment' }}
                                            </a>
                                        @endif
                                    </div>
                                    <div class="text-muted mt-1" style="font-size:0.7rem; text-align: {{ $isMe ? 'right' : 'left' }}">
                                        {{ $msg->created_at->format('M j, g:i A') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if($serviceOrder->messages->isEmpty())
                        <p class="text-muted text-center small py-3">No messages yet. Use the form below to communicate.</p>
                    @endif
                </div>

                @if(!$serviceOrder->isCompleted() && $serviceOrder->status->value !== 'cancelled')
                    <div class="card-footer bg-white">
                        @php
                            $msgRoute = $isBuyer
                                ? route('service-orders.message', $serviceOrder->id)
                                : route('vendor.service-orders.message', $serviceOrder->id);
                        @endphp
                        <form action="{{ $msgRoute }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex gap-2">
                                <div class="flex-grow-1">
                                    <textarea name="message" rows="2" class="form-control"
                                        placeholder="Type a message..."></textarea>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <label class="btn btn-outline-secondary btn-sm" title="Attach file">
                                        <i class="bi bi-paperclip"></i>
                                        <input type="file" name="attachment" class="d-none">
                                    </label>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-send"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Order Info --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Order Details</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Reference</dt>
                        <dd class="col-7 font-monospace">{{ $serviceOrder->reference }}</dd>

                        <dt class="col-5 text-muted">Package</dt>
                        <dd class="col-7">{{ $serviceOrder->package->name }}</dd>

                        <dt class="col-5 text-muted">Amount</dt>
                        <dd class="col-7 fw-bold text-primary">{{ money($serviceOrder->total_amount) }}</dd>

                        <dt class="col-5 text-muted">Delivery</dt>
                        <dd class="col-7">{{ $serviceOrder->package->delivery_days }} days</dd>

                        <dt class="col-5 text-muted">Due Date</dt>
                        <dd class="col-7 {{ $serviceOrder->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                            {{ $serviceOrder->due_at ? $serviceOrder->due_at->format('M j, Y') : '—' }}
                        </dd>

                        <dt class="col-5 text-muted">Revisions</dt>
                        <dd class="col-7">
                            {{ $serviceOrder->revisions_used }} / {{ $serviceOrder->revisions_allowed }}
                            used
                        </dd>

                        @if($serviceOrder->started_at)
                            <dt class="col-5 text-muted">Started</dt>
                            <dd class="col-7">{{ $serviceOrder->started_at->format('M j, Y') }}</dd>
                        @endif

                        @if($serviceOrder->completed_at)
                            <dt class="col-5 text-muted">Completed</dt>
                            <dd class="col-7">{{ $serviceOrder->completed_at->format('M j, Y') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            @if($serviceOrder->requirements)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">Requirements</div>
                    <div class="card-body small text-secondary">
                        {{ $serviceOrder->requirements }}
                    </div>
                </div>
            @endif

            {{-- The other party --}}
            @php
                $otherParty = $isBuyer ? $serviceOrder->vendor : null;
                $otherUser  = $isBuyer ? $serviceOrder->buyer : null;
            @endphp
            @if($isBuyer && $otherParty)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Vendor</div>
                    <div class="card-body">
                        <a href="{{ route('storefront.show', $otherParty->user->username) }}"
                           class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                            <img src="{{ $otherParty->logo_url }}"
                                 class="rounded-circle"
                                 style="width:40px;height:40px;object-fit:cover;">
                            <div>
                                <div class="fw-semibold small">{{ $otherParty->business_name }}</div>
                                <div class="text-muted" style="font-size:0.75rem;">View Store</div>
                            </div>
                        </a>
                    </div>
                </div>
            @elseif($isVendor && $otherUser)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Buyer</div>
                    <div class="card-body d-flex align-items-center gap-2 small">
                        <img src="{{ $otherUser->getAvatarUrlAttribute() ?? 'https://ui-avatars.com/api/?name=' . urlencode($otherUser->name) }}"
                             class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                        <span>{{ $otherUser->name }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-scroll message thread to bottom
const thread = document.getElementById('messageThread');
if (thread) thread.scrollTop = thread.scrollHeight;
</script>
@endpush
@endsection
