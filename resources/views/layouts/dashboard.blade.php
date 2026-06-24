@extends('layouts.app')

@php
    $user = auth()->user();
    $menu = \App\Support\Menu::pour($user);
    $notifRoutes = [
        'administrateur' => 'admin.notifications.index',
        'etudiant' => 'etudiant.notifications.index',
        'professeur' => 'professeur.notifications.index',
    ];
    $notifRoute = $notifRoutes[$user->role->value] ?? null;
    $notifCount = $user->unreadNotifications()->count();
    // Recherche globale unifiée pour le personnel (l'étudiant n'en a pas).
    $rechercheRoute = $user->role === \App\Enums\Role::Etudiant ? null : route('recherche.globale');
    $messagesNonLus = \App\Models\Message::nonLusPour($user->id)->count();
@endphp

@section('content')
<div class="ucao-shell">
    <aside class="ucao-sidebar" id="ucao-sidebar">
        <a href="{{ route('dashboard') }}" class="ucao-sidebar__brand">
            <i class="bi bi-mortarboard-fill fs-4"></i>
            <span>SIGE UCAO</span>
        </a>
        <nav class="ucao-nav">
            @foreach ($menu as $item)
                @php
                    $active = str_ends_with($item['route'], '.index')
                        ? request()->routeIs(\Illuminate\Support\Str::beforeLast($item['route'], '.').'.*')
                        : request()->routeIs($item['route']);
                @endphp
                <a href="{{ route($item['route']) }}" class="{{ $active ? 'active' : '' }}" @if ($active) aria-current="page" @endif>
                    <i class="bi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
        <div class="p-3 border-top">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-box-arrow-right me-1"></i>Se déconnecter
                </button>
            </form>
        </div>
    </aside>

    <div class="ucao-backdrop" onclick="ucaoToggleSidebar()"></div>

    <div class="ucao-main">
        <header class="ucao-topbar">
            <button class="ucao-icon-btn" onclick="ucaoToggleSidebar()" aria-label="Afficher/masquer le menu">
                <i class="bi bi-list"></i>
            </button>

            @if ($rechercheRoute)
                <div class="ucao-topbar__search d-none d-sm-block" id="recherche-globale"
                     data-suggestions-url="{{ route('recherche.suggestions') }}">
                    <form method="GET" action="{{ $rechercheRoute }}" role="search" autocomplete="off">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" name="q" value="{{ request('q') }}" class="form-control"
                                   placeholder="Rechercher un étudiant..." aria-label="Recherche"
                                   role="combobox" aria-expanded="false" aria-autocomplete="list"
                                   aria-controls="recherche-suggestions" autocomplete="off">
                        </div>
                        <ul class="recherche-suggestions" id="recherche-suggestions" role="listbox" hidden></ul>
                    </form>
                </div>
            @endif

            <div class="ms-auto d-flex align-items-center gap-2">
                <span class="ucao-icon-btn theme-toggle" onclick="ucaoToggleTheme()" title="Changer de thème" role="button" tabindex="0">
                    <i id="ucao-theme-icon" class="bi bi-moon-stars"></i>
                </span>
                <a href="{{ route('messagerie.index') }}" class="ucao-icon-btn" title="Messagerie" aria-label="Messagerie">
                    <i class="bi bi-envelope"></i>
                    @if ($messagesNonLus)
                        <span class="badge bg-primary rounded-pill">{{ $messagesNonLus }}</span>
                    @endif
                </a>
                @if ($notifRoute)
                    <a href="{{ route($notifRoute) }}" class="ucao-icon-btn" title="Notifications" aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                        @if ($notifCount)
                            <span class="badge bg-danger rounded-pill">{{ $notifCount }}</span>
                        @endif
                    </a>
                @endif
                <div class="dropdown">
                    <button class="btn d-flex align-items-center gap-2 p-1 border-0" data-bs-toggle="dropdown" aria-expanded="false">
                        @php $initiales = strtoupper(mb_substr($user->prenom ?? '', 0, 1).mb_substr($user->nom ?? '', 0, 1)); @endphp
                        @if ($user->photoUrl())
                            <img src="{{ $user->photoUrl() }}" alt="Photo de {{ $user->nom_complet }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                        @else
                            <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-semibold" style="width:36px;height:36px;font-size:.85rem;">{{ $initiales ?: '?' }}</span>
                        @endif
                        <span class="d-none d-md-inline small text-start lh-1">
                            <span class="fw-semibold d-block">{{ $user->nom_complet }}</span>
                            <span class="text-muted">{{ $user->role->label() }}</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="{{ route('compte.show') }}"><i class="bi bi-person-circle me-2"></i>Mon compte</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Se déconnecter</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="container-fluid p-3 p-lg-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
                <div>
                    <h1 class="h4 mb-1">@yield('page-title')</h1>
                    @hasSection('page-subtitle')
                        <p class="text-muted mb-0">@yield('page-subtitle')</p>
                    @endif
                </div>
                @hasSection('page-actions')
                    <div class="d-flex flex-wrap gap-2">@yield('page-actions')</div>
                @endif
            </div>

            <div class="ucao-page-content">
                @yield('page-content')
            </div>
        </main>
    </div>
</div>

{{-- Toasts --}}
<div class="ucao-toast-container">
    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $variant)
        @if (session($key))
            <div class="toast align-items-center text-bg-{{ $variant }} border-0 ucao-auto-toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">{{ session($key) }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
                </div>
            </div>
        @endif
    @endforeach
</div>
@endsection

@push('styles')
<style>
    .ucao-topbar__search { position: relative; }
    .recherche-suggestions {
        position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 1050;
        margin: 0; padding: 4px; list-style: none; max-height: 22rem; overflow-y: auto;
        background: var(--surface, #fff); border: 1px solid rgba(0,0,0,.1);
        border-radius: .5rem; box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }
    .recherche-suggestions__item {
        display: flex; flex-direction: column; gap: 2px; padding: .5rem .65rem;
        border-radius: .375rem; cursor: pointer; color: inherit; text-decoration: none;
    }
    .recherche-suggestions__item:hover,
    .recherche-suggestions__item.is-active {
        background: rgba(37, 99, 235, .1);
    }
    .recherche-suggestions__label { font-weight: 600; font-size: .9rem; }
    .recherche-suggestions__label mark { background: transparent; color: var(--bs-primary, #2563EB); padding: 0; font-weight: 700; }
    .recherche-suggestions__meta { font-size: .78rem; opacity: .7; }
    .recherche-suggestions__vide { padding: .6rem .65rem; font-size: .85rem; opacity: .7; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var racine = document.getElementById('recherche-globale');
    if (!racine) return;

    var input = racine.querySelector('input[name="q"]');
    var liste = racine.querySelector('#recherche-suggestions');
    var form  = racine.querySelector('form');
    var url   = racine.dataset.suggestionsUrl;

    var MIN = 2, DELAI = 200;
    var minuteur = null, controleur = null, items = [], actif = -1;

    function echapperHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // Surligne (sans accent/casse) la première occurrence du terme dans le libellé.
    function surligner(texte, terme) {
        var sansAccent = function (v) { return v.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase(); };
        var base = sansAccent(texte), cible = sansAccent(terme.trim().split(/\s+/)[0] || '');
        var i = cible ? base.indexOf(cible) : -1;
        if (i < 0) return echapperHtml(texte);
        return echapperHtml(texte.slice(0, i)) + '<mark>' + echapperHtml(texte.slice(i, i + cible.length)) + '</mark>' + echapperHtml(texte.slice(i + cible.length));
    }

    function fermer() {
        liste.hidden = true; liste.innerHTML = ''; items = []; actif = -1;
        input.setAttribute('aria-expanded', 'false');
    }

    function afficher(resultats, terme) {
        items = resultats;
        liste.innerHTML = '';
        if (resultats.length === 0) {
            var vide = document.createElement('li');
            vide.className = 'recherche-suggestions__vide';
            vide.textContent = 'Aucun résultat';
            liste.appendChild(vide);
        } else {
            resultats.forEach(function (r, idx) {
                var li = document.createElement('li');
                li.setAttribute('role', 'option');
                var a = document.createElement('a');
                a.className = 'recherche-suggestions__item';
                a.href = r.url;
                a.dataset.index = idx;
                a.innerHTML = '<span class="recherche-suggestions__label">' + surligner(r.label, terme) + '</span>' +
                              '<span class="recherche-suggestions__meta">' + echapperHtml(r.sous_titre || '') + '</span>';
                li.appendChild(a);
                liste.appendChild(li);
            });
        }
        liste.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        actif = -1;
    }

    function surlignerActif() {
        liste.querySelectorAll('.recherche-suggestions__item').forEach(function (el, idx) {
            el.classList.toggle('is-active', idx === actif);
            if (idx === actif) el.scrollIntoView({ block: 'nearest' });
        });
    }

    function rechercher(terme) {
        if (controleur) controleur.abort();
        controleur = new AbortController();
        fetch(url + '?q=' + encodeURIComponent(terme), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: controleur.signal,
        })
            .then(function (r) { return r.ok ? r.json() : []; })
            .then(function (data) { afficher(Array.isArray(data) ? data : [], terme); })
            .catch(function () { /* requête annulée ou erreur réseau : on ignore */ });
    }

    input.addEventListener('input', function () {
        var terme = input.value.trim();
        clearTimeout(minuteur);
        if (terme.length < MIN) { fermer(); return; }
        minuteur = setTimeout(function () { rechercher(terme); }, DELAI);
    });

    input.addEventListener('keydown', function (e) {
        var liens = liste.querySelectorAll('.recherche-suggestions__item');
        if (e.key === 'ArrowDown' && liens.length) {
            e.preventDefault(); actif = (actif + 1) % liens.length; surlignerActif();
        } else if (e.key === 'ArrowUp' && liens.length) {
            e.preventDefault(); actif = (actif - 1 + liens.length) % liens.length; surlignerActif();
        } else if (e.key === 'Enter') {
            // Une suggestion sélectionnée : on y va. Sinon : recherche complète (fallback).
            if (actif >= 0 && liens[actif]) { e.preventDefault(); window.location.href = liens[actif].href; }
        } else if (e.key === 'Escape') {
            fermer();
        }
    });

    document.addEventListener('click', function (e) {
        if (!racine.contains(e.target)) fermer();
    });

    form.addEventListener('submit', function () { fermer(); });
})();
</script>
@endpush
