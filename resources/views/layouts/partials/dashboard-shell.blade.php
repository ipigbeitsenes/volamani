{{--
    Enterprise dashboard shell — shared STRUCTURAL design system for all five
    dashboard layouts (admin, vendor, buyer/account, support, finance).
    It consumes each layout's own palette tokens so colours stay per-role:
      --vl-sidebar-bg   (dark chrome)   --vl-sidebar-active (accent)
      --vl-sidebar-width               (--vl-sidebar-hover, optional)
    Include in <head> AFTER the layout's own <style> so it takes precedence.
--}}
<style>
    :root {
        --vl-topbar-h: 64px;
        --vl-content-max: 1440px;
        --vl-radius: 14px;
        --vl-radius-sm: 10px;
        --vl-border: #e6e8ef;
        --vl-ink: #0f172a;
        --vl-body: #475569;
        --vl-muted: #64748b;
        --vl-surface: #f4f6fb;
        --vl-accent: var(--vl-sidebar-active, #1a56db);
        --vl-shadow-sm: 0 1px 2px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.06);
        --vl-shadow: 0 4px 12px -2px rgba(15,23,42,.08), 0 2px 6px -2px rgba(15,23,42,.05);
        --vl-shadow-md: 0 14px 30px -12px rgba(15,23,42,.18);
    }
    * { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
    body { background: var(--vl-surface); color: var(--vl-body); letter-spacing: -.01em; }
    h1, h2, h3, h4, h5, h6 { color: var(--vl-ink); letter-spacing: -.02em; }
    a { text-decoration: none; }

    /* ── Sidebar ────────────────────────────────────────────────────────── */
    .sidebar {
        width: var(--vl-sidebar-width, 260px);
        background: var(--vl-sidebar-bg, #0f172a);
        border-right: 1px solid rgba(255,255,255,.06);
        padding-bottom: 1.5rem;
    }
    .sidebar-brand {
        height: var(--vl-topbar-h);
        display: flex; align-items: center; gap: .6rem;
        padding: 0 1.25rem; margin-bottom: .35rem;
        font-weight: 800; font-size: 1.15rem; color: #fff; text-decoration: none;
        border-bottom: 1px solid rgba(255,255,255,.07);
        position: sticky; top: 0; z-index: 2;
        background: var(--vl-sidebar-bg, #0f172a);
    }
    .sidebar .nav-section {
        font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .09em;
        color: rgba(255,255,255,.4); padding: 1.05rem 1.5rem .35rem;
    }
    .sidebar .nav-link {
        position: relative;
        color: rgba(255,255,255,.68);
        padding: .55rem .8rem; margin: .1rem .7rem; border-radius: 10px;
        display: flex; align-items: center; gap: .7rem;
        font-size: .875rem; font-weight: 500; line-height: 1.25;
        border-left: 0 !important;
        transition: background .15s ease, color .15s ease;
    }
    .sidebar .nav-link i { font-size: 1.05rem; width: 1.3rem; text-align: center; flex-shrink: 0; }
    .sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,.08); }
    .sidebar .nav-link.active {
        color: #fff; font-weight: 600;
        background: rgba(255,255,255,.12);
        background: color-mix(in srgb, var(--vl-accent) 30%, transparent);
    }
    .sidebar .nav-link.active::before {
        content: ''; position: absolute; left: -.7rem; top: 22%; bottom: 22%;
        width: 3px; border-radius: 0 3px 3px 0; background: var(--vl-accent);
    }
    .sidebar .nav-link .badge { margin-left: auto; }

    /* ── Main + topbar ──────────────────────────────────────────────────── */
    .main-content { margin-left: var(--vl-sidebar-width, 260px); min-height: 100vh; display: flex; flex-direction: column; }
    .topbar {
        height: var(--vl-topbar-h); min-height: var(--vl-topbar-h);
        background: rgba(255,255,255,.86);
        backdrop-filter: saturate(180%) blur(12px); -webkit-backdrop-filter: saturate(180%) blur(12px);
        border-bottom: 1px solid var(--vl-border);
        padding: 0 1.25rem; position: sticky; top: 0; z-index: 99;
    }
    .topbar .breadcrumb { --bs-breadcrumb-divider: '\203A'; margin: 0; }
    .page-content { padding: 1.5rem; width: 100%; max-width: var(--vl-content-max); margin-inline: auto; }
    @media (max-width: 575.98px) { .page-content { padding: 1rem; } }
    /* Pages built for the public layout wrap themselves in .container; inside the
       dashboard shell that would double the gutters, so neutralise it. */
    .page-content > .container,
    .page-content > .container-fluid,
    .page-content > .container-lg,
    .page-content > .container-xl { max-width: 100%; padding-left: 0; padding-right: 0; }

    /* ── Page header ────────────────────────────────────────────────────── */
    .vl-page-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
    .vl-page-head h1, .vl-page-head h2, .vl-page-head h4 { margin: 0; font-weight: 800; font-size: 1.4rem; }
    .vl-page-head .vl-sub { color: var(--vl-muted); font-size: .9rem; margin: .15rem 0 0; }

    /* ── Cards ──────────────────────────────────────────────────────────── */
    .card { border: 1px solid var(--vl-border); border-radius: var(--vl-radius); box-shadow: var(--vl-shadow-sm); }
    .card.shadow-sm { box-shadow: var(--vl-shadow) !important; }
    .card > .card-header { background: #fff; border-bottom: 1px solid var(--vl-border); font-weight: 700; padding: 1rem 1.15rem; }
    .hover-lift { transition: transform .2s ease, box-shadow .2s ease; }
    .hover-lift:hover { transform: translateY(-3px); box-shadow: var(--vl-shadow-md); }

    /* ── KPI stat tiles ─────────────────────────────────────────────────── */
    .vl-stat { position: relative; border: 1px solid var(--vl-border); border-radius: var(--vl-radius); background: #fff; padding: 1.1rem 1.15rem; box-shadow: var(--vl-shadow-sm); height: 100%; transition: box-shadow .2s, transform .2s; }
    .vl-stat:hover { box-shadow: var(--vl-shadow-md); transform: translateY(-2px); }
    .vl-stat__ico { width: 42px; height: 42px; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: .7rem;
        background: rgba(26,86,219,.1); background: color-mix(in srgb, var(--vl-accent) 13%, #fff); color: var(--vl-accent); }
    .vl-stat__label { color: var(--vl-muted); font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .vl-stat__value { font-size: 1.55rem; font-weight: 800; color: var(--vl-ink); line-height: 1.05; margin-top: .1rem; }
    .vl-stat__foot { font-size: .8rem; margin-top: .45rem; }

    /* ── Tables ─────────────────────────────────────────────────────────── */
    .table { --bs-table-border-color: var(--vl-border); margin: 0; }
    .table > thead th { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: var(--vl-muted); font-weight: 700; border-bottom: 1px solid var(--vl-border); padding: .75rem 1rem; white-space: nowrap; background: transparent; }
    .table > tbody td { padding: .8rem 1rem; vertical-align: middle; border-color: var(--vl-border); }
    .table-hover > tbody > tr:hover > * { background: var(--vl-surface); }

    /* ── Misc refinements ───────────────────────────────────────────────── */
    .badge { font-weight: 600; letter-spacing: .01em; }
    .btn { border-radius: 9px; font-weight: 600; }
    .btn-sm { border-radius: 8px; }
    .list-group-item { border-color: var(--vl-border); }
    .vl-empty { text-align: center; color: var(--vl-muted); padding: 3rem 1rem; }
    .vl-empty i { font-size: 2.2rem; opacity: .35; display: block; margin-bottom: .5rem; }
    ::selection { background: color-mix(in srgb, var(--vl-accent) 20%, transparent); }

    /* Mobile menu toggle — clear, tappable affordance in the topbar. */
    #sidebarToggle {
        width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 10px; border: 1px solid var(--vl-border); background: #fff;
        color: var(--vl-ink); font-size: 1.2rem; padding: 0; flex-shrink: 0;
    }
    #sidebarToggle:hover, #sidebarToggle[aria-expanded="true"] {
        background: color-mix(in srgb, var(--vl-accent) 12%, #fff); color: var(--vl-accent); border-color: transparent;
    }
</style>
