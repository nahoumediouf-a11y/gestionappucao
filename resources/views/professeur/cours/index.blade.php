@extends('layouts.dashboard')

@section('title', 'Cours en ligne — SIGE UCAO')

@section('page-title', 'Mes cours en ligne')

@section('page-actions')
    <a href="{{ route('professeur.cours.create') }}" class="btn btn-ucao">
        <i class="bi bi-camera-video me-1"></i>Planifier une séance
    </a>
@endsection

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Titre</th>
                    <th>Classe</th>
                    <th>Début prévu</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cours as $seance)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $seance->titre }}</div>
                            @if ($seance->emploiDuTemps)
                                <small class="text-muted">{{ $seance->emploiDuTemps->matiere }}</small>
                            @endif
                        </td>
                        <td>{{ $seance->filiere }} {{ $seance->niveau }}</td>
                        <td>{{ $seance->debut_prevu->format('d/m/Y H:i') }}</td>
                        <td><span class="badge bg-{{ $seance->statutCouleur() }}">{{ $seance->statutLabel() }}</span></td>
                        <td class="text-end">
                            @if ($seance->statut === 'planifie')
                                <form method="POST" action="{{ route('professeur.cours.demarrer', $seance) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-play-fill"></i> Démarrer
                                    </button>
                                </form>
                                <a href="{{ route('professeur.cours.edit', $seance) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('professeur.cours.destroy', $seance) }}" class="d-inline" onsubmit="return confirm('Supprimer cette séance ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @elseif ($seance->statut === 'en_cours')
                                <a href="{{ route('professeur.cours.salle', $seance) }}" class="btn btn-sm btn-success">
                                    <i class="bi bi-camera-video-fill"></i> Rejoindre
                                </a>
                                <form method="POST" action="{{ route('professeur.cours.terminer', $seance) }}" class="d-inline" onsubmit="return confirm('Terminer cette séance ?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-stop-fill"></i> Terminer
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune séance en ligne planifiée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
