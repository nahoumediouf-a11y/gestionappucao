@extends('layouts.app')

@section('content')
<nav class="navbar navbar-dark navbar-ucao navbar-expand-lg mb-4">
    <div class="container">
        <a href="{{ route('dashboard') }}" class="navbar-brand mb-0 h1 text-decoration-none">
            <i class="bi bi-bank2 me-2"></i>SIGE UCAO
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ucaoNavbar" aria-controls="ucaoNavbar" aria-expanded="false" aria-label="Afficher le menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="ucaoNavbar">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 text-white ms-lg-auto mt-3 mt-lg-0 pb-2 pb-lg-0">
                <span class="small">
                    <i class="bi bi-person-badge me-1"></i>
                    {{ auth()->user()->nom_complet }}
                    <span class="badge bg-light text-dark ms-1">{{ auth()->user()->role->label() }}</span>
                </span>
                <span class="theme-toggle fs-5" onclick="ucaoToggleTheme()" title="Changer de thème">
                    <i id="ucao-theme-icon" class="bi bi-moon-stars"></i>
                </span>
                <a href="{{ route('compte.show') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-person-circle me-1"></i>Mon compte
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>Se déconnecter
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div class="container pb-5">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h2 class="h4 mb-1">@yield('page-title')</h2>
            @hasSection('page-subtitle')
                <p class="text-muted mb-0">@yield('page-subtitle')</p>
            @endif
        </div>
        @hasSection('page-actions')
            <div>@yield('page-actions')</div>
        @endif
    </div>

    @if (! request()->routeIs('dashboard'))
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Retour au tableau de bord
        </a>
    @endif

    <div class="ucao-page-content">
        @yield('page-content')
    </div>
</div>
@endsection
