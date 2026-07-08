@php($vlChat = app(\App\Services\Chat\ChatService::class))
@if($vlChat->isEnabled())
@php($vlChatCfg = $vlChat->widgetConfig())
<div id="vlc-root"
     data-authed="{{ auth()->check() ? '1' : '0' }}"
     data-greeting="{{ $vlChatCfg['greeting'] }}"
     data-welcome="{{ $vlChatCfg['welcome'] }}"
     data-team="{{ $vlChatCfg['teamName'] }}"
     data-start-url="{{ route('chat.start') }}"
     data-message-url="{{ url('chat') }}"
     data-csrf="{{ csrf_token() }}">

    <!-- Greeting bubble -->
    <div class="vlc-greet" id="vlc-greet" role="button" tabindex="0" aria-label="Open chat">
        <button type="button" class="vlc-greet-close" id="vlc-greet-close" aria-label="Dismiss">&times;</button>
        <span class="vlc-greet-text"></span>
    </div>

    <!-- Launcher -->
    <button type="button" class="vlc-launcher" id="vlc-launcher" aria-label="Chat with us">
        <span class="vlc-pulse"></span>
        <i class="bi bi-chat-dots-fill vlc-ic-open"></i>
        <i class="bi bi-x-lg vlc-ic-close"></i>
        <span class="vlc-badge" id="vlc-badge" hidden>1</span>
    </button>

    <!-- Panel -->
    <div class="vlc-panel" id="vlc-panel" aria-hidden="true">
        <div class="vlc-head">
            <div class="vlc-head-avatar"><i class="bi bi-headset"></i></div>
            <div class="vlc-head-meta">
                <strong class="vlc-team"></strong>
                <span class="vlc-status"><span class="vlc-dot"></span> Typically replies in a few minutes</span>
            </div>
            <button type="button" class="vlc-head-x" id="vlc-close" aria-label="Close chat"><i class="bi bi-chevron-down"></i></button>
        </div>

        <div class="vlc-body" id="vlc-body">
            <div class="vlc-welcome" id="vlc-welcome"></div>
        </div>

        <div class="vlc-guest" id="vlc-guest" hidden>
            <input type="text" class="vlc-inp" id="vlc-name" placeholder="Your name (optional)" maxlength="80" autocomplete="name">
            <input type="email" class="vlc-inp" id="vlc-email" placeholder="Email (optional)" maxlength="120" autocomplete="email">
        </div>

        <form class="vlc-foot" id="vlc-form" autocomplete="off">
            <textarea id="vlc-input" class="vlc-textarea" rows="1" placeholder="Type your message…" maxlength="2000"></textarea>
            <button type="submit" class="vlc-send" id="vlc-send" aria-label="Send"><i class="bi bi-send-fill"></i></button>
        </form>
    </div>
</div>

<style>
    :root { --vlc-primary: #1a56db; --vlc-primary2: #4f46e5; }
    #vlc-root { position: fixed; right: 20px; bottom: 20px; z-index: 1090; font-family: 'Inter', system-ui, sans-serif; }

    /* Launcher */
    .vlc-launcher {
        position: relative; width: 60px; height: 60px; border-radius: 50%;
        border: none; cursor: pointer; color: #fff; font-size: 1.55rem;
        background: linear-gradient(135deg, var(--vlc-primary) 0%, var(--vlc-primary2) 100%);
        box-shadow: 0 12px 26px -8px rgba(26,86,219,.6); display: grid; place-items: center;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .vlc-launcher:hover { transform: translateY(-3px) scale(1.04); box-shadow: 0 18px 34px -10px rgba(26,86,219,.7); }
    .vlc-launcher .vlc-ic-close { display: none; }
    .vlc-pulse { position: absolute; inset: 0; border-radius: 50%; background: var(--vlc-primary);
        animation: vlcPulse 2s ease-out infinite; z-index: -1; }
    @keyframes vlcPulse { 0% { transform: scale(1); opacity: .55; } 70% { transform: scale(1.7); opacity: 0; } 100% { opacity: 0; } }
    .vlc-badge { position: absolute; top: -3px; right: -3px; min-width: 20px; height: 20px; padding: 0 5px;
        background: #dc2626; color: #fff; border-radius: 999px; font-size: .7rem; font-weight: 700;
        display: grid; place-items: center; box-shadow: 0 0 0 2px #fff; }

    /* Open state */
    #vlc-root.vlc-open .vlc-ic-open { display: none; }
    #vlc-root.vlc-open .vlc-ic-close { display: block; }
    #vlc-root.vlc-open .vlc-pulse { display: none; }
    #vlc-root.vlc-open .vlc-greet { display: none; }

    /* Greeting bubble */
    .vlc-greet {
        position: absolute; right: 76px; bottom: 8px; width: 250px; background: #fff; color: #0f172a;
        border: 1px solid #e8ecf4; border-radius: 16px; padding: .85rem 2rem .85rem 1rem; font-size: .875rem;
        line-height: 1.45; box-shadow: 0 18px 40px -14px rgba(15,23,42,.28); cursor: pointer;
        opacity: 0; transform: translateY(8px) scale(.96); pointer-events: none; transition: all .28s cubic-bezier(.16,1,.3,1);
    }
    .vlc-greet.vlc-show { opacity: 1; transform: none; pointer-events: auto; }
    .vlc-greet::after { content: ''; position: absolute; right: -7px; bottom: 18px; width: 14px; height: 14px;
        background: #fff; border-right: 1px solid #e8ecf4; border-bottom: 1px solid #e8ecf4; transform: rotate(-45deg); }
    .vlc-greet-close { position: absolute; top: 4px; right: 8px; border: none; background: none; font-size: 1.1rem;
        line-height: 1; color: #94a3b8; cursor: pointer; }

    /* Panel */
    .vlc-panel {
        position: absolute; right: 0; bottom: 76px; width: 366px; max-width: calc(100vw - 40px); height: 520px;
        max-height: calc(100vh - 120px); background: #fff; border-radius: 20px; overflow: hidden;
        display: flex; flex-direction: column; box-shadow: 0 30px 60px -18px rgba(15,23,42,.4);
        opacity: 0; transform: translateY(14px) scale(.97); pointer-events: none; transition: all .26s cubic-bezier(.16,1,.3,1);
    }
    #vlc-root.vlc-open .vlc-panel { opacity: 1; transform: none; pointer-events: auto; }
    .vlc-head { display: flex; align-items: center; gap: .7rem; padding: .9rem 1rem; color: #fff;
        background: linear-gradient(135deg, var(--vlc-primary) 0%, var(--vlc-primary2) 100%); }
    .vlc-head-avatar { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,.18);
        display: grid; place-items: center; font-size: 1.15rem; }
    .vlc-head-meta { display: flex; flex-direction: column; line-height: 1.25; flex: 1; min-width: 0; }
    .vlc-head-meta strong { font-size: .95rem; }
    .vlc-status { font-size: .72rem; opacity: .9; display: flex; align-items: center; gap: .35rem; }
    .vlc-dot { width: 8px; height: 8px; border-radius: 50%; background: #4ade80; box-shadow: 0 0 0 3px rgba(74,222,128,.3); }
    .vlc-head-x { margin-left: auto; border: none; background: rgba(255,255,255,.15); color: #fff; width: 30px; height: 30px;
        border-radius: 8px; cursor: pointer; display: grid; place-items: center; }

    .vlc-body { flex: 1; overflow-y: auto; padding: 1rem; background: #f5f7fc; display: flex; flex-direction: column; gap: .55rem; }
    .vlc-welcome { align-self: flex-start; background: #fff; border: 1px solid #e8ecf4; border-radius: 14px 14px 14px 4px;
        padding: .65rem .85rem; font-size: .85rem; color: #334155; max-width: 85%; box-shadow: 0 2px 6px rgba(15,23,42,.04); }

    .vlc-msg { max-width: 82%; padding: .55rem .8rem; font-size: .86rem; line-height: 1.45; border-radius: 14px;
        white-space: pre-wrap; word-wrap: break-word; box-shadow: 0 2px 6px rgba(15,23,42,.05); }
    .vlc-msg .vlc-meta { display: block; font-size: .66rem; margin-top: .25rem; opacity: .7; }
    .vlc-msg.vlc-them { align-self: flex-start; background: #fff; color: #1e293b; border: 1px solid #e8ecf4; border-bottom-left-radius: 4px; }
    .vlc-msg.vlc-me { align-self: flex-end; color: #fff; border-bottom-right-radius: 4px;
        background: linear-gradient(135deg, var(--vlc-primary) 0%, var(--vlc-primary2) 100%); }
    .vlc-msg.vlc-bot { align-self: flex-start; background: #fffbeb; border: 1px solid #fde68a; color: #713f12; border-bottom-left-radius: 4px; }
    .vlc-msg.vlc-them .vlc-who { display: block; font-size: .68rem; font-weight: 700; color: var(--vlc-primary); margin-bottom: .15rem; }
    .vlc-msg.vlc-bot .vlc-who { display: block; font-size: .68rem; font-weight: 700; color: #b45309; margin-bottom: .15rem; }

    .vlc-guest { display: flex; gap: .4rem; padding: .55rem .75rem 0; }
    .vlc-inp { flex: 1; border: 1px solid #e8ecf4; border-radius: 9px; padding: .4rem .6rem; font-size: .78rem; }
    .vlc-inp:focus { outline: none; border-color: var(--vlc-primary); }

    .vlc-foot { display: flex; align-items: flex-end; gap: .5rem; padding: .7rem .75rem; border-top: 1px solid #eef1f7; background: #fff; }
    .vlc-textarea { flex: 1; resize: none; border: 1px solid #e8ecf4; border-radius: 12px; padding: .55rem .75rem;
        font-size: .86rem; max-height: 96px; font-family: inherit; }
    .vlc-textarea:focus { outline: none; border-color: var(--vlc-primary); box-shadow: 0 0 0 3px rgba(26,86,219,.12); }
    .vlc-send { border: none; width: 42px; height: 42px; border-radius: 12px; color: #fff; cursor: pointer; flex-shrink: 0;
        background: linear-gradient(135deg, var(--vlc-primary) 0%, var(--vlc-primary2) 100%); display: grid; place-items: center; }
    .vlc-send:disabled { opacity: .5; cursor: not-allowed; }

    @media (max-width: 480px) {
        #vlc-root { right: 14px; bottom: 14px; }
        .vlc-panel { width: calc(100vw - 28px); }
        .vlc-greet { display: none !important; }
    }
</style>

<script>
(function () {
    const root = document.getElementById('vlc-root');
    if (!root) return;

    const authed   = root.dataset.authed === '1';
    const startUrl = root.dataset.startUrl;
    const baseUrl  = root.dataset.messageUrl; // /chat
    const csrf     = root.dataset.csrf;
    const team     = root.dataset.team || 'Support';

    const launcher = document.getElementById('vlc-launcher');
    const panel    = document.getElementById('vlc-panel');
    const body     = document.getElementById('vlc-body');
    const form     = document.getElementById('vlc-form');
    const input    = document.getElementById('vlc-input');
    const sendBtn  = document.getElementById('vlc-send');
    const greet    = document.getElementById('vlc-greet');
    const badge    = document.getElementById('vlc-badge');
    const guestBox = document.getElementById('vlc-guest');

    document.querySelector('.vlc-team').textContent = team;
    greet.querySelector('.vlc-greet-text').textContent = root.dataset.greeting || 'Need help?';
    document.getElementById('vlc-welcome').textContent = root.dataset.welcome || '';

    const TOKEN_KEY = 'vlc_token';
    let token = authed ? null : localStorage.getItem(TOKEN_KEY);
    let lastId = 0;
    let opened = false;
    let starting = false;
    let identitySaved = false;
    let pollTimer = null;

    if (!authed) guestBox.hidden = false;

    function headers() {
        return { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' };
    }

    function scrollDown() { body.scrollTop = body.scrollHeight; }

    function render(msg) {
        if (msg.id <= lastId) return;
        lastId = msg.id;
        const el = document.createElement('div');
        const mine = msg.type === 'visitor';
        el.className = 'vlc-msg ' + (mine ? 'vlc-me' : (msg.type === 'bot' ? 'vlc-bot' : 'vlc-them'));
        if (!mine) {
            const who = document.createElement('span');
            who.className = 'vlc-who';
            who.textContent = msg.type === 'bot' ? (team + ' · auto') : team;
            el.appendChild(who);
        }
        el.appendChild(document.createTextNode(msg.body));
        const meta = document.createElement('span');
        meta.className = 'vlc-meta';
        meta.textContent = msg.time || '';
        el.appendChild(meta);
        body.appendChild(el);
        scrollDown();
    }

    function renderMany(list) { (list || []).forEach(render); }

    async function start() {
        if (starting) return;
        starting = true;
        try {
            const res = await fetch(startUrl, { method: 'POST', headers: headers(),
                body: JSON.stringify({ token: token }) });
            if (!res.ok) return;
            const data = await res.json();
            token = data.token;
            if (!authed && token) localStorage.setItem(TOKEN_KEY, token);
            lastId = 0;
            renderMany(data.messages);
        } finally { starting = false; }
    }

    async function poll() {
        if (!token) return;
        try {
            const res = await fetch(baseUrl + '/' + encodeURIComponent(token) + '/messages?after=' + lastId, {
                headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            renderMany(data.messages);
        } catch (e) { /* transient */ }
    }

    async function persistIdentity() {
        if (authed || identitySaved) return;
        const name = document.getElementById('vlc-name').value.trim();
        const email = document.getElementById('vlc-email').value.trim();
        if (!name && !email) return;
        try {
            await fetch(startUrl, { method: 'POST', headers: headers(),
                body: JSON.stringify({ token: token, name: name, email: email }) });
            identitySaved = true;
        } catch (e) { /* non-blocking */ }
    }

    async function open() {
        root.classList.add('vlc-open');
        panel.setAttribute('aria-hidden', 'false');
        badge.hidden = true;
        greet.classList.remove('vlc-show');
        if (!opened) {
            opened = true;
            await start();
            pollTimer = setInterval(poll, 5000);
        }
        setTimeout(() => input.focus(), 200);
    }

    function close() {
        root.classList.remove('vlc-open');
        panel.setAttribute('aria-hidden', 'true');
    }

    function toggle() { root.classList.contains('vlc-open') ? close() : open(); }

    launcher.addEventListener('click', toggle);
    document.getElementById('vlc-close').addEventListener('click', close);

    greet.addEventListener('click', open);
    document.getElementById('vlc-greet-close').addEventListener('click', function (e) {
        e.stopPropagation();
        greet.classList.remove('vlc-show');
        sessionStorage.setItem('vlc_greet_dismissed', '1');
    });

    // Auto-textarea grow + Enter-to-send.
    input.addEventListener('input', function () {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 96) + 'px';
    });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const bodyText = input.value.trim();
        if (!bodyText) return;
        sendBtn.disabled = true;
        try {
            if (!token) await start();
            await persistIdentity();
            const res = await fetch(baseUrl + '/' + encodeURIComponent(token) + '/message', {
                method: 'POST', headers: headers(), body: JSON.stringify({ body: bodyText }) });
            if (res.ok) {
                const data = await res.json();
                render(data.message);
                input.value = '';
                input.style.height = 'auto';
                setTimeout(poll, 1200);
            }
        } finally { sendBtn.disabled = false; input.focus(); }
    });

    // Nudge the visitor with the greeting bubble after a short delay.
    if (!sessionStorage.getItem('vlc_greet_dismissed')) {
        setTimeout(function () {
            if (!root.classList.contains('vlc-open')) greet.classList.add('vlc-show');
        }, 3500);
    }
})();
</script>
@endif
