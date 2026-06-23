@extends('layouts.dashboard')

@section('title', 'Liste des étudiants — SIGE UCAO')

@section('page-title', 'Liste des étudiants')
@section('page-subtitle', "Étudiants des filières/niveaux que vous enseignez.")

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Étudiant</th>
                    <th>Filière</th>
                    <th>Niveau</th>
                    <th>Situation</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($etudiants as $etudiant)
                    <tr>
                        <td>
                            @if ($etudiant->user)
                                @include('partials._identite', ['identite' => $etudiant->user, 'taille' => 'sm', 'sousTitre' => $etudiant->matricule])
                            @else
                                <code>{{ $etudiant->matricule }}</code>
                            @endif
                        </td>
                        <td>{{ $etudiant->filiere }}</td>
                        <td>{{ $etudiant->niveau }}</td>
                        <td>
                            @if ($etudiant->enSituationRouge())
                                <span class="badge bg-danger">Situation rouge ({{ $etudiant->absencesNonJustifieesCount() }} abs.)</span>
                            @else
                                <span class="badge bg-success">OK</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucun étudiant trouvé pour vos cours.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
