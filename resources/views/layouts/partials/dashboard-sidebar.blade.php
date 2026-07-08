{{--
    Shared responsive + scrollable behaviour for every dashboard sidebar.
    Include once near the end of <body> in a dashboard layout that provides:
      • an element  #sidebar.sidebar
      • a toggle button #sidebarToggle (give it class d-lg-none)
      • a .main-content wrapper
      • a --vl-sidebar-width CSS var
    Outputs the backdrop, styles and script INLINE (not via @push) so it works
    from a body include regardless of where the layout's @stack('styles') sits.
    Breakpoint: < 992px = off-canvas drawer.
--}}
<div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>

<style>
    /* Always scroll internally; dvh avoids the mobile address-bar cutoff. */
    .sidebar {
        height: 100vh;
        height: 100dvh;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }
    .sidebar::-webkit-scrollbar { width: 8px; }
    .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 8px; }
    .sidebar:hover::-webkit-scrollbar-thumb { background: rgba(255,255,255,.32); }
    .sidebar { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.28) transparent; }

    .sidebar-backdrop {
        position: fixed; inset: 0; background: rgba(15,23,42,.55);
        z-index: 1039; opacity: 0; visibility: hidden; transition: opacity .25s ease;
    }
    body.sidebar-open .sidebar-backdrop { opacity: 1; visibility: visible; }

    /* Tablet & phone: off-canvas drawer. */
    @media (max-width: 991.98px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform .3s ease;
            z-index: 1045;
            box-shadow: 0 1rem 3rem rgba(0,0,0,.35);
        }
        body.sidebar-open .sidebar { transform: translateX(0); }
        body.sidebar-open { overflow: hidden; }
        .main-content { margin-left: 0 !important; }
    }
    /* Desktop: sidebar always visible, never a drawer. */
    @media (min-width: 992px) {
        .sidebar { transform: none !important; }
        .sidebar-backdrop { display: none; }
        #sidebarToggle { display: none !important; }
    }
</style>

<script>
(function () {
    var body = document.body,
        toggle = document.getElementById('sidebarToggle'),
        backdrop = document.getElementById('sidebarBackdrop'),
        sidebar = document.getElementById('sidebar'),
        icon = toggle ? toggle.querySelector('i') : null;

    function sync() {
        var open = body.classList.contains('sidebar-open');
        if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (icon) icon.className = open ? 'bi bi-x-lg' : 'bi bi-list';
    }
    function close() { body.classList.remove('sidebar-open'); sync(); }

    if (toggle) {
        toggle.setAttribute('aria-controls', 'sidebar');
        toggle.setAttribute('aria-label', 'Toggle menu');
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            body.classList.toggle('sidebar-open');
            sync();
        });
    }
    if (backdrop) backdrop.addEventListener('click', close);
    // Tapping a link on mobile navigates away — close the drawer.
    if (sidebar) sidebar.addEventListener('click', function (e) {
        if (window.innerWidth < 992 && e.target.closest('a[href]')) close();
    });
    window.addEventListener('resize', function () { if (window.innerWidth >= 992) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    sync();
})();
</script>
