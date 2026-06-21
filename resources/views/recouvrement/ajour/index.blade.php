@extends('layouts.dashboard')

@section('title', 'Étudiants à jour — SIGE UCAO')
@section('page-title', 'Étudiants à jour')
@section('page-subtitle', 'Liste des étudiants ayant entièrement réglé leur scolarité.')

@section('page-content')

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-4">
            <div class="display-6 fw-bold text-success">{{ $etudiants->count() }}</div>
            <div class="text-muted small">Étudiants à jour</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-4">
            <div class="display-6 fw-bold text-primary">
                {{ number_format($etudiants->sum(fn($e) => $e->paiements->sum('montant')), 0, ',', ' ') }}
            </div>
            <div class="text-muted small">FCFA collectés (total)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-4">
            <div class="display-6 fw-bold text-info">
                {{ $etudiants->groupBy('filiere')->count() }}
            </div>
            <div class="text-muted small">Filières représentées</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Matricule</th>
                    <th>Étudiant</th>
                    <th>Filière / Niveau</th>
                    <th>Total payé</th>
                    <th>Dernier paiement</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($etudiants as $etudiant)
                    @php
                        $dernierPaiement = $etudiant->paiements->first();
                        $totalPaye = $etudiant->paiements->sum('montant');
                    @endphp
                    <tr>
                        <td><code>{{ $etudiant->matricule }}</code></td>
                        <td class="fw-semibold">{{ $etudiant->user->nom_complet }}</td>
                        <td>{{ $etudiant->filiere }} {{ $etudiant->niveau }}</td>
                        <td class="text-success fw-bold">{{ number_format($totalPaye, 0, ',', ' ') }} FCFA</td>
                        <td>
                            @if ($dernierPaiement)
                                {{ $dernierPaiement->date_paiement->format('d/m/Y') }}
                                <br><small class="text-muted">{{ $dernierPaiement->reference }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>À jour
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            Aucun étudiant n'a encore soldé sa scolarité.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
