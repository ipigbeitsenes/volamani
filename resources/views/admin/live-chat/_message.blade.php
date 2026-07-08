@php
    use App\Enums\ChatSenderType;
    $isVisitor = $m->sender_type === ChatSenderType::Visitor;
    $isBot     = $m->sender_type === ChatSenderType::Bot;
    if ($isVisitor) {
        $who = 'Visitor'; $bg = '#fff'; $border = '1px solid #e8ecf4'; $color = '#1e293b';
    } elseif ($isBot) {
        $who = 'Assistant · auto'; $bg = '#fffbeb'; $border = '1px solid #fde68a'; $color = '#713f12';
    } elseif ($m->sender_type === ChatSenderType::System) {
        $who = 'System'; $bg = '#f1f5f9'; $border = '1px solid #e2e8f0'; $color = '#475569';
    } else {
        $who = $m->sender?->name ?? 'Agent'; $bg = 'linear-gradient(135deg,#1a56db,#4f46e5)'; $border = 'none'; $color = '#fff';
    }
@endphp
<div class="d-flex mb-2 {{ $isVisitor ? 'justify-content-start' : 'justify-content-end' }}">
    <div style="max-width:78%; padding:.5rem .75rem; border-radius:12px; font-size:.86rem; white-space:pre-wrap; word-wrap:break-word; background:{{ $bg }}; border:{{ $border }}; color:{{ $color }};">
        <div style="font-size:.66rem; font-weight:700; opacity:.85;">{{ $who }}</div>
        {{ $m->body }}
        <div style="font-size:.64rem; opacity:.7; margin-top:.2rem;">{{ $m->created_at->format('g:i A') }}</div>
    </div>
</div>
