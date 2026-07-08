@extends('layouts.admin')

@section('title', 'Conversation')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.live-chat.index') }}">Live Chat</a></li>
    <li class="breadcrumb-item active">{{ $conversation->visitorName() }}</li>
@endsection

@section('content')

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            {{-- Header --}}
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <div>
                    <h6 class="fw-bold mb-0">{{ $conversation->visitorName() }}</h6>
                    <span class="small text-muted">{{ $conversation->visitorEmail() ?? 'No email provided' }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $conversation->status->badge() }}" id="conv-status">{{ $conversation->status->label() }}</span>
                    @if($conversation->isClosed())
                        <form method="POST" action="{{ route('admin.live-chat.reopen', $conversation) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-clockwise"></i> Reopen</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.live-chat.close', $conversation) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-check2-all"></i> Close</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Thread --}}
            <div class="card-body" id="chat-thread" style="height: 460px; overflow-y: auto; background:#f5f7fc;">
                @foreach($messages as $m)
                    @include('admin.live-chat._message', ['m' => $m])
                @endforeach
            </div>

            {{-- Reply --}}
            <div class="card-footer bg-white">
                <form id="reply-form" class="d-flex gap-2" data-url="{{ route('admin.live-chat.reply', $conversation) }}">
                    @csrf
                    <textarea name="body" id="reply-body" class="form-control" rows="1" maxlength="2000"
                              placeholder="Type your reply…" {{ $conversation->isClosed() ? 'disabled' : '' }}></textarea>
                    <button type="submit" class="btn btn-primary" {{ $conversation->isClosed() ? 'disabled' : '' }}>
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
                @if($conversation->isClosed())
                    <div class="small text-muted mt-2">This conversation is closed. Reopen it to reply.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar meta --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Details</h6></div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted fw-normal">Visitor</dt>
                    <dd class="col-7">{{ $conversation->visitorName() }}</dd>
                    <dt class="col-5 text-muted fw-normal">Email</dt>
                    <dd class="col-7">{{ $conversation->visitorEmail() ?? '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Type</dt>
                    <dd class="col-7">{{ $conversation->user_id ? 'Member' : 'Guest' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Agent</dt>
                    <dd class="col-7">{{ $conversation->agent?->name ?? 'Unassigned' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Started</dt>
                    <dd class="col-7">{{ $conversation->created_at->format('d M Y, g:i A') }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const thread   = document.getElementById('chat-thread');
    const form     = document.getElementById('reply-form');
    const bodyEl   = document.getElementById('reply-body');
    const statusEl = document.getElementById('conv-status');
    const csrf     = '{{ csrf_token() }}';
    const pollUrl  = '{{ route('admin.live-chat.poll', $conversation) }}';
    const replyUrl = form.dataset.url;
    let lastId     = {{ $messages->max('id') ?? 0 }};

    const STATUS = { open: 'Open', pending: 'Pending', closed: 'Closed' };
    const BADGE  = { open: 'danger', pending: 'warning', closed: 'secondary' };

    function scrollDown() { thread.scrollTop = thread.scrollHeight; }
    scrollDown();

    function bubble(msg) {
        if (msg.id <= lastId) return;
        lastId = msg.id;
        const team = msg.type !== 'visitor';
        const wrap = document.createElement('div');
        wrap.className = 'd-flex mb-2 ' + (team ? 'justify-content-end' : 'justify-content-start');
        const box = document.createElement('div');
        box.style.maxWidth = '78%';
        box.style.padding = '.5rem .75rem';
        box.style.borderRadius = '12px';
        box.style.fontSize = '.86rem';
        box.style.whiteSpace = 'pre-wrap';
        box.style.wordWrap = 'break-word';
        if (msg.type === 'visitor') { box.style.background = '#fff'; box.style.border = '1px solid #e8ecf4'; }
        else if (msg.type === 'bot') { box.style.background = '#fffbeb'; box.style.border = '1px solid #fde68a'; box.style.color = '#713f12'; }
        else { box.style.background = 'linear-gradient(135deg,#1a56db,#4f46e5)'; box.style.color = '#fff'; }
        const who = document.createElement('div');
        who.style.fontSize = '.66rem'; who.style.fontWeight = '700'; who.style.opacity = '.85';
        who.textContent = msg.type === 'visitor' ? 'Visitor' : (msg.type === 'bot' ? 'Assistant · auto' : (msg.name || 'Agent'));
        box.appendChild(who);
        box.appendChild(document.createTextNode(msg.body));
        const t = document.createElement('div');
        t.style.fontSize = '.64rem'; t.style.opacity = '.7'; t.style.marginTop = '.2rem';
        t.textContent = msg.time || '';
        box.appendChild(t);
        wrap.appendChild(box);
        thread.appendChild(wrap);
        scrollDown();
    }

    function setStatus(s) {
        if (!STATUS[s]) return;
        statusEl.textContent = STATUS[s];
        statusEl.className = 'badge bg-' + BADGE[s];
    }

    async function poll() {
        try {
            const res = await fetch(pollUrl + '?after=' + lastId, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            (data.messages || []).forEach(bubble);
            setStatus(data.status);
        } catch (e) { /* transient */ }
    }
    setInterval(poll, 5000);

    bodyEl.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const text = bodyEl.value.trim();
        if (!text) return;
        const btn = form.querySelector('button');
        btn.disabled = true;
        try {
            const res = await fetch(replyUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ body: text })
            });
            if (res.ok) {
                const data = await res.json();
                bubble(data.message);
                setStatus('pending');
                bodyEl.value = '';
            }
        } finally { btn.disabled = false; bodyEl.focus(); }
    });
})();
</script>
@endpush
@endsection
