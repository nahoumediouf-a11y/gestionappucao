@extends('layouts.dashboard')

@section('title', 'Cours en ligne — SIGE UCAO')

@section('page-title', 'Cours en ligne')

@section('page-subtitle', 'Séances de visioconférence de votre classe ('.$etudiant->filiere.' '.$etudiant->niveau.')')

@section('page-content')
<div class="row g-3">
    @forelse ($cours as $seance)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-{{ $seance->statutCouleur() }}">{{ $seance->statutLabel() }}</span>
                        <small class="text-muted">{{ $seance->debut_prevu->format('d/m/Y H:i') }}</small>
                    </div>
                    <h3 class="h6 mb-1">{{ $seance->titre }}</h3>
                    <p class="text-muted small mb-2">
                        <i class="bi bi-person-video3 me-1"></i>{{ $seance->professeur->nom_complet }}
                    </p>
                    @if ($seance->description)
                        <p class="small text-muted flex-grow-1">{{ \Illuminate\Support\Str::limit($seance->description, 100) }}</p>
                    @else
                        <div class="flex-grow-1"></div>
                    @endif

                    @if ($seance->estRejoignable())
                        <a href="{{ route('etudiant.cours.salle', $seance) }}" class="btn btn-success btn-sm mt-2">
                            <i class="bi bi-camera-video-fill me-1"></i>Rejoindre
                        </a>
                    @elseif ($seance->statut === 'planifie')
                        <button class="btn btn-outline-secondary btn-sm mt-2" disabled>
                            <i class="bi bi-clock me-1"></i>Pas encore ouverte
                        </button>
                    @else
                        <button class="btn btn-outline-secondary btn-sm mt-2" disabled>
                            <i class="bi bi-slash-circle me-1"></i>{{ $seance->statutLabel() }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info mb-0">Aucun cours en ligne prévu pour votre classe pour le moment.</div>
        </div>
    @endforelse
</div>
@endsection
