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
                <form class="ucao-topbar__search d-none d-sm-block" method="GET" action="{{ $rechercheRoute }}">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher un étudiant..." aria-label="Recherche">
                    </div>
                </form>
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
