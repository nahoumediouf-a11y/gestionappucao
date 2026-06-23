<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGE UCAO')</title>
    <meta name="theme-color" content="#1e3a8a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SIGE UCAO">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-180x180.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/icon-16x16.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script>
        (function () {
            var theme = localStorage.getItem('ucao-theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <style>
        :root {
            --ucao-blue: #2563eb;
            --ucao-blue-dark: #1d4ed8;
            --ucao-gold: #f59e0b;
            --ucao-green: #10b981;
            --ucao-orange: #f59e0b;
            --ucao-red: #ef4444;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e5e7eb;
            --radius: 18px;
            --radius-sm: 12px;
            --shadow-sm: 0 1px 2px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.06);
            --shadow-md: 0 4px 12px rgba(15,23,42,.06), 0 2px 4px rgba(15,23,42,.04);
            --sidebar-w: 256px;
        }

        [data-theme="dark"] {
            --bg: #0f172a;
            --surface: #1e293b;
            --text: #e5e7eb;
            --text-muted: #94a3b8;
            --border: #334155;
            --ucao-blue: #3b82f6;
            --ucao-blue-dark: #2563eb;
        }

        body {
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            transition: background-color .3s ease, color .3s ease;
        }

        [data-theme="dark"] .card,
        [data-theme="dark"] .table,
        [data-theme="dark"] .modal-content,
        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background-color: var(--surface);
            color: var(--text);
            border-color: var(--border);
        }
        [data-theme="dark"] .table {
            --bs-table-bg: var(--surface);
            --bs-table-color: var(--text);
            --bs-table-border-color: var(--border);
            --bs-table-hover-bg: #273449;
            --bs-table-hover-color: var(--text);
        }
        [data-theme="dark"] .table-light,
        [data-theme="dark"] thead.table-light th {
            --bs-table-bg: #273449;
            --bs-table-color: var(--text);
            background-color: #273449;
            color: var(--text);
            border-color: var(--border);
        }
        [data-theme="dark"] .table-hover > tbody > tr:hover > * {
            background-color: #273449;
            color: var(--text);
        }
        [data-theme="dark"] .text-muted { color: var(--text-muted) !important; }
        [data-theme="dark"] .bg-white { background-color: var(--surface) !important; }
        [data-theme="dark"] .bg-light { background-color: #273449 !important; color: var(--text); }
        [data-theme="dark"] .border { border-color: var(--border) !important; }
        [data-theme="dark"] .btn-outline-secondary { color: var(--text); border-color: var(--border); }

        [data-theme="dark"] .bg-light.text-dark,
        [data-theme="dark"] .rounded-circle.text-dark {
            color: var(--text) !important;
        }
        [data-theme="dark"] .rounded-circle.bg-dark.bg-opacity-10 {
            background-color: rgba(255, 255, 255, .12) !important;
        }
        [data-theme="dark"] .text-secondary {
            color: #94a3b8 !important;
        }
        [data-theme="dark"] .rounded-circle.bg-secondary.bg-opacity-10 {
            background-color: rgba(148, 163, 184, .18) !important;
        }

        [data-theme="dark"] .form-control::placeholder {
            color: var(--text-muted);
            opacity: 1;
        }

        [data-theme="dark"] .input-group-text {
            background-color: #273449;
            color: var(--text);
            border-color: var(--border);
        }

        [data-theme="dark"] .page-link {
            background-color: var(--surface);
            color: var(--text);
            border-color: var(--border);
        }
        [data-theme="dark"] .page-item.disabled .page-link {
            background-color: var(--surface);
            color: var(--text-muted);
            border-color: var(--border);
        }
        [data-theme="dark"] .page-item.active .page-link {
            background-color: var(--ucao-blue);
            border-color: var(--ucao-blue);
            color: #fff;
        }

        .auth-wrapper {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 55%, #1e40af 100%);
        }
        .auth-wrapper::before {
            content: '';
            position: absolute;
            inset: -20px;
            background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1740&q=80');
            background-size: cover;
            background-position: center;
            filter: blur(6px) brightness(.65) saturate(1.1);
            transform: scale(1.05);
            animation: ucao-bg-pan 35s ease-in-out infinite alternate;
            z-index: 0;
        }
        .auth-wrapper::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(30,58,138,.55) 0%, rgba(37,99,235,.40) 55%, rgba(30,64,175,.58) 100%);
            z-index: 1;
        }
        .auth-wrapper > .container,
        .auth-wrapper > .theme-toggle {
            position: relative;
            z-index: 2;
        }
        @keyframes ucao-bg-pan {
            from { transform: scale(1.08) translate(0, 0); }
            to { transform: scale(1.15) translate(-1.5%, -1.5%); }
        }
        .auth-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
            overflow: hidden;
            animation: ucao-fade-up .6s ease both;
        }
        .auth-brand {
            background: rgba(255,255,255,.10);
            backdrop-filter: blur(6px);
            color: #fff;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .auth-brand i.fs-2,
        .auth-brand i.fs-1 {
            animation: ucao-float 3.5s ease-in-out infinite;
        }
        .auth-form-panel {
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(10px);
            color: var(--text);
        }
        [data-theme="dark"] .auth-form-panel {
            background: rgba(30,41,59,.92);
        }
        @keyframes ucao-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .btn-ucao {
            background: var(--ucao-blue);
            border-color: var(--ucao-blue);
            color: #fff;
            border-radius: 10px;
            transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
        }
        .btn-ucao:hover {
            background: var(--ucao-blue-dark);
            border-color: var(--ucao-blue-dark);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(37,99,235,.25);
        }
        .navbar-ucao { background: var(--ucao-blue); }

        /* ===== Cartes & rayons modernes ===== */
        .card { border-radius: var(--radius) !important; }
        .card.shadow-sm { box-shadow: var(--shadow-sm) !important; }
        .btn { border-radius: 10px; }
        .form-control, .form-select, .input-group-text { border-radius: 10px; }
        .input-group .form-control:not(:first-child) { border-top-left-radius: 0; border-bottom-left-radius: 0; }
        .input-group .input-group-text:first-child { border-top-right-radius: 0; border-bottom-right-radius: 0; }

        /* ===== Shell sidebar + topbar ===== */
        .ucao-shell { display: flex; min-height: 100vh; }
        .ucao-sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 1040;
            display: flex; flex-direction: column;
            transition: transform .25s ease;
            overflow-y: auto;
        }
        .ucao-sidebar__brand {
            display: flex; align-items: center; gap: .5rem;
            padding: 1.1rem 1.25rem; font-weight: 700; color: var(--ucao-blue);
            border-bottom: 1px solid var(--border); text-decoration: none;
        }
        .ucao-nav { padding: .75rem; flex-grow: 1; }
        .ucao-nav a {
            display: flex; align-items: center; gap: .75rem;
            padding: .6rem .8rem; margin-bottom: .15rem;
            border-radius: 12px; color: var(--text); text-decoration: none;
            font-size: .925rem; transition: background-color .15s ease, color .15s ease;
        }
        .ucao-nav a:hover { background: rgba(37,99,235,.08); color: var(--ucao-blue); }
        .ucao-nav a.active { background: var(--ucao-blue); color: #fff; box-shadow: 0 4px 10px rgba(37,99,235,.3); }
        .ucao-nav a i { font-size: 1.05rem; width: 1.2rem; text-align: center; }
        .ucao-nav__section { font-size: .7rem; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); padding: .75rem .8rem .35rem; }

        .ucao-main { flex-grow: 1; margin-left: var(--sidebar-w); min-width: 0; transition: margin-left .25s ease; }
        .ucao-topbar {
            position: sticky; top: 0; z-index: 1030;
            background: color-mix(in srgb, var(--surface) 85%, transparent);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 1rem;
            padding: .65rem 1.25rem;
        }
        .ucao-topbar__search { flex-grow: 1; max-width: 420px; }
        .ucao-icon-btn {
            width: 40px; height: 40px; border-radius: 12px; border: 1px solid var(--border);
            background: var(--surface); color: var(--text); display: inline-flex;
            align-items: center; justify-content: center; cursor: pointer; position: relative;
            transition: background-color .15s ease, transform .12s ease;
        }
        .ucao-icon-btn:hover { background: rgba(37,99,235,.08); color: var(--ucao-blue); }
        .ucao-icon-btn .badge { position: absolute; top: -4px; right: -4px; font-size: .6rem; }

        .ucao-backdrop { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.45); z-index: 1035; }

        @media (max-width: 991.98px) {
            .ucao-sidebar { transform: translateX(-100%); }
            .ucao-main { margin-left: 0; }
            body.ucao-sidebar-open .ucao-sidebar { transform: translateX(0); }
            body.ucao-sidebar-open .ucao-backdrop { display: block; }
        }
        body.ucao-sidebar-collapsed .ucao-sidebar { transform: translateX(-100%); }
        @media (min-width: 992px) {
            body.ucao-sidebar-collapsed .ucao-main { margin-left: 0; }
        }

        /* ===== Toasts ===== */
        .ucao-toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 1080; }

        /* ===== Animations ===== */
        @keyframes ucao-fade-up {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes ucao-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .ucao-page-content > * {
            animation: ucao-fade-up .45s ease both;
        }

        .ucao-fade-up {
            animation: ucao-fade-up .6s ease both;
        }

        .card {
            transition: transform .15s ease, box-shadow .15s ease, background-color .3s ease, border-color .3s ease;
        }
        .card.shadow-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.08) !important;
        }

        .btn {
            transition: transform .12s ease, box-shadow .12s ease, background-color .15s ease, border-color .15s ease, color .15s ease;
        }
        .btn:active { transform: scale(.97); }

        .table-hover > tbody > tr {
            transition: background-color .12s ease;
        }

        .theme-toggle {
            cursor: pointer;
            transition: transform .3s ease;
        }
        .theme-toggle:hover { transform: rotate(20deg); }
        [data-theme="dark"] .theme-toggle i { animation: ucao-spin .6s ease; }

        /* ===== Responsive (mobile) ===== */
        @media (max-width: 767.98px) {
            .auth-brand {
                background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
                border-radius: 1rem 1rem 0 0;
            }
        }
        @media (max-width: 575.98px) {
            .container { padding-left: .75rem; padding-right: .75rem; }
            .card-body { padding: 1rem; }
            h2.h4 { font-size: 1.15rem; }
            .table { font-size: .85rem; }
            .btn-sm { padding: .25rem .5rem; font-size: .8rem; }
            .auth-brand { padding: 1.25rem; }
            .display-6 { font-size: 1.75rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function ucaoSetThemeIcon(theme) {
            var icon = document.getElementById('ucao-theme-icon');
            if (!icon) return;
            icon.classList.remove('bi-moon-stars', 'bi-sun');
            icon.classList.add(theme === 'dark' ? 'bi-sun' : 'bi-moon-stars');
        }
        function ucaoToggleTheme() {
            var html = document.documentElement;
            var current = html.getAttribute('data-theme') || 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('ucao-theme', next);
            ucaoSetThemeIcon(next);
        }
        function ucaoToggleSidebar() {
            var mobile = window.matchMedia('(max-width: 991.98px)').matches;
            document.body.classList.toggle(mobile ? 'ucao-sidebar-open' : 'ucao-sidebar-collapsed');
        }
        document.addEventListener('DOMContentLoaded', function () {
            ucaoSetThemeIcon(document.documentElement.getAttribute('data-theme') || 'light');
            // Affichage automatique des toasts (notifications flash).
            if (window.bootstrap) {
                document.querySelectorAll('.ucao-auto-toast').forEach(function (el) {
                    var t = new bootstrap.Toast(el, { delay: 4000 });
                    t.show();
                });
            }
            // Fix mobile: ensure touch events trigger properly
            var toggles = document.querySelectorAll('[onclick="ucaoToggleTheme()"]');
            toggles.forEach(function(el) {
                el.style.cursor = 'pointer';
                el.style.webkitTapHighlightColor = 'transparent';
                el.addEventListener('touchend', function(e) {
                    e.preventDefault();
                    ucaoToggleTheme();
                });
            });
        });
    </script>
    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js')
                    .then(function (reg) { console.log('SW registered:', reg.scope); })
                    .catch(function (err) { console.warn('SW failed:', err); });
            });
        }
    </script>
</body>
</html>
