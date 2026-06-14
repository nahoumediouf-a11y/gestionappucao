@extends('layouts.dashboard')

@section('title', 'Projets de classe — Recouvrement UCAO')

@section('page-title', 'Projets de classe')
@section('page-subtitle', 'Projets prescrits par vos professeurs.')

@section('page-content')
@forelse ($projets as $projet)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h3 class="h6 mb-1">{{ $projet->titre }}</h3>
                    <div class="text-muted small mb-2">
                        {{ $projet->matiere }} — {{ $projet->filiere }} {{ $projet->niveau }}
                        @if ($projet->professeur)
                            · Prof. {{ $projet->professeur->nom_complet }}
                        @endif
                    </div>
                    @if ($projet->description)
                        <p class="mb-0">{{ $projet->description }}</p>
                    @endif
                </div>
                <div class="text-end">
                    <span class="badge bg-{{ $projet->statut() === 'Terminé' ? 'secondary' : 'success' }} mb-1">
                        {{ $projet->statut() }}
                    </span>
                    <div class="small text-muted">Échéance : {{ $projet->date_limite->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-info">Aucun projet de classe n'a été assigné pour le moment.</div>
@endforelse
@endsection
