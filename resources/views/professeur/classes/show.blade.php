@extends('layouts.dashboard')

@section('title', 'Classe '.$filiere.' '.$niveau.' — SIGE UCAO')

@section('page-title', 'Classe '.$filiere.' '.$niveau)
@section('page-subtitle', $effectif.' étudiant(s)')

@section('page-actions')
    @if ($matieres->isNotEmpty())
        <a href="{{ route('professeur.carnet.index', ['filiere' => $filiere, 'niveau' => $niveau]) }}" class="btn btn-ucao">
            <i class="bi bi-table me-1"></i>Carnet de notes
        </a>
    @endif
@endsection

@section('page-content')
{{-- Indicateurs --}}
<div class="row g-3 mb-4">
    @php
        $cartes = [
            ['Effectif', $effectif, 'dark'],
            ['Moyenne de classe', $moyenneClasse !== null ? $moyenneClasse.' /20' : '—', $moyenneClasse !== null && $moyenneClasse < 10 ? 'danger' : 'success'],
            ['Taux de rendu (dernier travail)', $tauxRendu !== null ? $tauxRendu.' %' : '—', 'info'],
            ['Étudiants à risque', $aRisque->count(), $aRisque->count() ? 'danger' : 'success'],
        ];
    @endphp
    @foreach ($cartes as [$label, $valeur, $couleur])
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="h4 mb-0 text-{{ $couleur }}">{{ $valeur }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Actions rapides --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap gap-2">
        <a href="{{ route('professeur.notes.create') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-journal-plus me-1"></i>Saisir une note</a>
        <a href="{{ route('professeur.absences.create') }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-calendar-x me-1"></i>Marquer une absence</a>
        <a href="{{ route('professeur.cours.create') }}" class="btn btn-sm btn-outline-info"><i class="bi bi-camera-video me-1"></i>Cours en ligne</a>
        <a href="{{ route('professeur.projets.create') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-kanban me-1"></i>Assigner un travail</a>
        <a href="{{ route('professeur.documents_cours.create') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-arrow-up me-1"></i>Publier un cours</a>
    </div>
</div>

{{-- Liste des étudiants --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Étudiant</th>
                    <th>Moyenne</th>
                    <th>Absences non justifiées</th>
                    <th>Situation</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($etudiants as $e)
                    @php
                        $moy = $moyennes[$e->id] ?? null;
                        $abs = $absencesNonJustifiees[$e->id] ?? 0;
                        $risque = $abs >= $seuilAbsences || ($moy !== null && $moy < 10);
                    @endphp
                    <tr class="{{ $risque ? 'table-danger' : '' }}">
                        <td>
                            @if ($e->user)
                                @include('partials._identite', ['identite' => $e->user, 'taille' => 'sm', 'sousTitre' => $e->matricule])
                            @else
                                <span class="text-muted">{{ $e->matricule }}</span>
                            @endif
                        </td>
                        <td>{{ $moy !== null ? $moy.' /20' : '—' }}</td>
                        <td>{{ $abs }}</td>
                        <td>
                            @if ($risque)
                                <span class="badge bg-danger">À risque</span>
                            @else
                                <span class="badge bg-success">OK</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucun étudiant dans cette classe.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<a href="{{ route('professeur.espace') }}" class="btn btn-outline-secondary mt-3">
    <i class="bi bi-arrow-left me-1"></i>Retour à mon espace
</a>
@endsection
