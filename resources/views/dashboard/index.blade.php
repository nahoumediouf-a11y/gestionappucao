@extends('layouts.dashboard')

@section('title', 'Tableau de bord — Recouvrement UCAO')

@section('page-title', 'Tableau de bord')
@section('page-subtitle', "Modules accessibles selon votre rôle dans le diagramme de cas d'utilisation.")

@section('page-content')
<div class="row g-3">
    @forelse ($modules as $module)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route($module['route']) }}" class="text-decoration-none text-reset">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-{{ $module['color'] }} bg-opacity-10 text-{{ $module['color'] }} p-3">
                                <i class="bi {{ $module['icon'] }} fs-4"></i>
                            </div>
                            <div>
                                <h3 class="h6 mb-1">{{ $module['label'] }}</h3>
                                <p class="small text-muted mb-0">Module autorisé pour {{ $user->role->label() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning">Aucun module accessible.</div>
        </div>
    @endforelse
</div>
@endsection
