@extends('layouts.dashboard')

@section('title', 'Rechercher un étudiant — SIGE UCAO')

@section('page-title', 'Rechercher un étudiant')

@section('page-content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-8">
                <input type="text" name="q" class="form-control" placeholder="Matricule, nom ou prénom..." value="{{ $q }}" autofocus>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-ucao w-100"><i class="bi bi-search"></i> Rechercher</button>
            </div>
        </form>
    </div>
</div>

@if ($q->isNotEmpty())
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matricule</th>
                        <th>Étudiant</th>
                        <th>Filière / Niveau</th>
                        <th>Solde</th>
                        <th>Paiements</th>
                        <th>Engagements</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($resultats as $etudiant)
                        <tr>
                            <td><code>{{ $etudiant->matricule }}</code></td>
                            <td>{{ $etudiant->user->nom_complet }}</td>
                            <td>{{ $etudiant->filiere }} {{ $etudiant->niveau }}</td>
                            <td class="fw-bold {{ $etudiant->solde > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($etudiant->solde, 0, ',', ' ') }} FCFA
                            </td>
                            <td>{{ $etudiant->paiements->count() }} paiement(s)</td>
                            <td>{{ $etudiant->engagements->count() }} engagement(s)</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun étudiant trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
