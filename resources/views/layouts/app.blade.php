<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGE UCAO')</title>
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
            --ucao-blue: #1e3a8a;
            --ucao-gold: #d97706;
            --bg: #f4f6fb;
            --surface: #ffffff;
            --text: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
        }

        [data-theme="dark"] {
            --bg: #0f172a;
            --surface: #1e293b;
            --text: #e5e7eb;
            --text-muted: #94a3b8;
            --border: #334155;
            --ucao-blue: #1e3a8a;
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
        [data-theme="dark"] .table-light,
        [data-theme="dark"] thead.table-light th {
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
            background: var(--ucao-gold);
            border-color: var(--ucao-gold);
            color: #fff;
            transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
        }
        .btn-ucao:hover {
            background: #b45309;
            border-color: #b45309;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(0,0,0,.15);
        }
        .navbar-ucao { background: var(--ucao-blue); }

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
        function ucaoToggleTheme() {
            var html = document.documentElement;
            var current = html.getAttribute('data-theme') || 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('ucao-theme', next);
            var icon = document.getElementById('ucao-theme-icon');
            if (icon) {
                icon.classList.toggle('bi-moon-stars', next === 'light');
                icon.classList.toggle('bi-sun', next === 'dark');
            }
        }
        document.addEventListener('DOMContentLoaded', function () {
            var icon = document.getElementById('ucao-theme-icon');
            if (icon) {
                var theme = document.documentElement.getAttribute('data-theme') || 'light';
                icon.classList.toggle('bi-moon-stars', theme === 'light');
                icon.classList.toggle('bi-sun', theme === 'dark');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
