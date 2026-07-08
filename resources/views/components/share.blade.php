@props([
    'url',                 // absolute URL to share
    'title' => '',         // text/title of the thing being shared
    'align' => 'end',      // dropdown alignment: start | end
    'size' => 'md',        // md | sm
    'label' => true,       // show text labels next to icons
    'copyButton' => true,  // show the standalone copy-link button (copy is always in the dropdown too)
])

@php
    $shareUrl   = $url;
    $shareTitle = trim($title);
    $enc     = rawurlencode($shareUrl);
    $encT    = rawurlencode($shareTitle);
    $encMsg  = rawurlencode(trim($shareTitle . "\n" . $shareUrl));
    $btnSize = $size === 'sm' ? 'btn-sm' : '';

    // name, icon, brand colour, href
    $platforms = [
        ['WhatsApp', 'bi-whatsapp',   '#25D366', "https://wa.me/?text={$encMsg}"],
        ['Facebook', 'bi-facebook',   '#1877F2', "https://www.facebook.com/sharer/sharer.php?u={$enc}"],
        ['X',        'bi-twitter-x',  '#0f172a', "https://twitter.com/intent/tweet?url={$enc}&text={$encT}"],
        ['LinkedIn', 'bi-linkedin',   '#0A66C2', "https://www.linkedin.com/sharing/share-offsite/?url={$enc}"],
        ['Telegram', 'bi-telegram',   '#229ED9', "https://t.me/share/url?url={$enc}&text={$encT}"],
        ['Email',    'bi-envelope-fill', '#64748b', "mailto:?subject={$encT}&body={$encMsg}"],
    ];
@endphp

@once
@push('styles')
<style>
    .vl-share { display: inline-flex; align-items: center; gap: .5rem; }
    .vl-share .btn { border-radius: 50rem; }
    .vl-share-menu { min-width: 264px; border-radius: 16px; border: 1px solid var(--vl-border,#e8ecf4); }
    .vl-share-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .35rem; }
    .vl-share-item {
        display: flex; flex-direction: column; align-items: center; gap: .35rem;
        padding: .6rem .25rem; border-radius: 12px; text-decoration: none;
        color: var(--vl-body,#334155); transition: background .15s, transform .15s;
    }
    .vl-share-item:hover { background: rgba(15,23,42,.05); transform: translateY(-2px); color: var(--vl-body,#334155); }
    .vl-share-ico {
        width: 42px; height: 42px; border-radius: 50%; display: inline-flex;
        align-items: center; justify-content: center; font-size: 1.15rem;
        color: #fff; background: var(--sc, #64748b);
    }
    .vl-share-name { font-size: .72rem; font-weight: 600; }
    .vl-copy-group .form-control { background: #f8fafc; font-size: .8rem; }
    .vl-copy-btn.vl-copied { background: var(--vl-success,#059669) !important; border-color: var(--vl-success,#059669) !important; color:#fff !important; }
    .vl-share-toast {
        position: fixed; left: 50%; bottom: 26px; transform: translateX(-50%) translateY(12px);
        background: #0f172a; color: #fff; padding: .6rem 1.05rem; border-radius: 50rem;
        font-size: .85rem; font-weight: 600; box-shadow: 0 12px 30px -8px rgba(15,23,42,.5);
        display: inline-flex; align-items: center; gap: .5rem; z-index: 1090;
        opacity: 0; pointer-events: none; transition: opacity .25s, transform .25s;
    }
    .vl-share-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    @media (prefers-color-scheme: dark) {
        .vl-share-item:hover { background: rgba(255,255,255,.07); }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function (resolve, reject) {
            try {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.top = '-9999px';
                document.body.appendChild(ta);
                ta.focus(); ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                resolve();
            } catch (e) { reject(e); }
        });
    }

    var toastEl;
    function toast(msg) {
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.className = 'vl-share-toast';
            document.body.appendChild(toastEl);
        }
        toastEl.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + msg;
        toastEl.classList.add('show');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(function () { toastEl.classList.remove('show'); }, 1900);
    }

    document.addEventListener('click', function (e) {
        var copyBtn = e.target.closest('.vl-copy-btn');
        if (copyBtn) {
            e.preventDefault();
            copyText(copyBtn.getAttribute('data-copy')).then(function () {
                var icon = copyBtn.querySelector('i');
                var prev = icon ? icon.className : null;
                if (icon) icon.className = 'bi bi-check-lg';
                copyBtn.classList.add('vl-copied');
                toast('Link copied');
                setTimeout(function () {
                    if (icon && prev) icon.className = prev;
                    copyBtn.classList.remove('vl-copied');
                }, 1600);
            }).catch(function () { toast('Press Ctrl+C to copy'); });
            return;
        }

        var nativeBtn = e.target.closest('.vl-share-native');
        if (nativeBtn && navigator.share) {
            e.preventDefault();
            navigator.share({
                title: nativeBtn.getAttribute('data-title') || document.title,
                text:  nativeBtn.getAttribute('data-title') || '',
                url:   nativeBtn.getAttribute('data-url')
            }).catch(function () {});
        }
    });

    // Reveal the native "More apps" option only where the Web Share API exists.
    function revealNative() {
        if (navigator.share) {
            document.querySelectorAll('.vl-share-native').forEach(function (el) {
                el.classList.remove('d-none');
            });
        }
    }
    if (document.readyState !== 'loading') revealNative();
    else document.addEventListener('DOMContentLoaded', revealNative);
})();
</script>
@endpush
@endonce

<div class="vl-share">
    {{-- Share dropdown --}}
    <div class="dropdown">
        <button class="btn btn-outline-secondary {{ $btnSize }} dropdown-toggle" type="button"
                data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                aria-label="Share">
            <i class="bi bi-share-fill"></i>@if($label)<span class="ms-1 d-none d-sm-inline">Share</span>@endif
        </button>
        <div class="dropdown-menu dropdown-menu-{{ $align }} vl-share-menu shadow p-3">
            <div class="small fw-bold text-uppercase text-muted mb-2" style="letter-spacing:.06em;">Share this</div>
            <div class="vl-share-grid mb-2">
                @foreach($platforms as [$name, $icon, $color, $href])
                    <a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
                       class="vl-share-item" style="--sc: {{ $color }}">
                        <span class="vl-share-ico"><i class="bi {{ $icon }}"></i></span>
                        <span class="vl-share-name">{{ $name }}</span>
                    </a>
                    @if($loop->last)
                        {{-- Native OS share sheet (mobile) — hidden unless supported --}}
                        <button type="button" class="vl-share-item vl-share-native d-none border-0 bg-transparent"
                                data-url="{{ $shareUrl }}" data-title="{{ $shareTitle }}">
                            <span class="vl-share-ico" style="--sc:#4f46e5;"><i class="bi bi-three-dots"></i></span>
                            <span class="vl-share-name">More</span>
                        </button>
                    @endif
                @endforeach
            </div>
            <div class="input-group input-group-sm vl-copy-group">
                <input type="text" class="form-control" value="{{ $shareUrl }}" readonly
                       onclick="this.select()" aria-label="Share link">
                <button class="btn btn-primary vl-copy-btn" type="button" data-copy="{{ $shareUrl }}" title="Copy link">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Standalone copy-link button --}}
    @if($copyButton)
        <button class="btn btn-outline-secondary {{ $btnSize }} vl-copy-btn" type="button"
                data-copy="{{ $shareUrl }}" aria-label="Copy link" title="Copy link">
            <i class="bi bi-clipboard"></i>@if($label)<span class="ms-1 d-none d-sm-inline">Copy link</span>@endif
        </button>
    @endif
</div>
