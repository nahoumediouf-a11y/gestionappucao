@extends('layouts.dashboard')

@section('title', 'Impayés — Recouvrement UCAO')

@section('page-title', 'Étudiants en situation d\'impayé')
@section('page-subtitle', 'Liste des étudiants ayant un solde restant à payer.')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Matricule</th>
                    <th>Étudiant</th>
                    <th>Filière / Niveau</th>
                    <th>Solde restant</th>
                    <th>Engagements</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($impayes as $etudiant)
                    <tr>
                        <td><code>{{ $etudiant->matricule }}</code></td>
                        <td>{{ $etudiant->user->nom_complet }}</td>
                        <td>{{ $etudiant->filiere }} {{ $etudiant->niveau }}</td>
                        <td class="text-danger fw-bold">{{ number_format($etudiant->solde, 0, ',', ' ') }} FCFA</td>
                        <td>
                            @forelse ($etudiant->engagements as $engagement)
                                <span class="badge bg-{{ $engagement->statut === 'honore' ? 'success' : ($engagement->statut === 'relance' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($engagement->statut) }} — {{ $engagement->echeance->format('d/m/Y') }}
                                </span>
                            @empty
                                <span class="text-muted small">Aucun</span>
                            @endforelse
                        </td>
                        <td class="text-end">
                            <a href="{{ route('recouvrement.engagements.create') }}" class="btn btn-sm btn-ucao">
                                <i class="bi bi-file-earmark-text me-1"></i>Créer un engagement
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun impayé. Tous les étudiants sont à jour.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
