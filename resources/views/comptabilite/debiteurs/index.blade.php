@extends('layouts.dashboard')

@section('title', 'Étudiants débiteurs — SIGE UCAO')

@section('page-title', 'Étudiants débiteurs')
@section('page-subtitle', 'Étudiants ayant un solde restant à payer.')

@section('page-content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control" placeholder="Rechercher par matricule ou nom..." value="{{ $q }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Rechercher</button>
            </div>
        </form>
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
                    <th>Solde restant</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($debiteurs as $etudiant)
                    <tr>
                        <td><code>{{ $etudiant->matricule }}</code></td>
                        <td>{{ $etudiant->user->nom_complet }}</td>
                        <td>{{ $etudiant->filiere }} {{ $etudiant->niveau }}</td>
                        <td class="text-danger fw-bold">{{ number_format($etudiant->solde, 0, ',', ' ') }} FCFA</td>
                        <td class="text-end">
                            <a href="{{ route('comptabilite.paiements.create') }}" class="btn btn-sm btn-ucao">
                                <i class="bi bi-cash-coin me-1"></i>Enregistrer un paiement
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucun étudiant débiteur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($debiteurs->hasPages())
        <div class="card-footer bg-white">
            {{ $debiteurs->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
