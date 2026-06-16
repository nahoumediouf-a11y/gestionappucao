@extends('layouts.dashboard')

@section('title', 'Paiements — SIGE UCAO')

@section('page-title', 'Historique des paiements')

@section('page-actions')
    <a href="{{ route('comptabilite.paiements.create') }}" class="btn btn-ucao">
        <i class="bi bi-plus-circle me-1"></i>Enregistrer un paiement
    </a>
@endsection

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
                    <th>Référence</th>
                    <th>Étudiant</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Mode</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paiements as $paiement)
                    <tr>
                        <td><code>{{ $paiement->reference }}</code></td>
                        <td>{{ $paiement->etudiant->user->nom_complet }} ({{ $paiement->etudiant->matricule }})</td>
                        <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                        <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $paiement->modeLabel() }}</td>
                        <td>
                            @php $s = \App\Models\Paiement::STATUTS[$paiement->statut] ?? ['label' => $paiement->statut, 'color' => 'secondary']; @endphp
                            <span class="badge bg-{{ $s['color'] }}">{{ $s['label'] }}</span>
                            @if ($paiement->note_etudiant)
                                <br><small class="text-muted">{{ $paiement->note_etudiant }}</small>
                            @endif
                            @if ($paiement->numero_mobile)
                                <br><small class="text-muted"><i class="bi bi-phone"></i> {{ $paiement->numero_mobile }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($paiement->statut === 'en_attente_validation')
                                <form method="POST" action="{{ route('comptabilite.paiements.valider', $paiement) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-success" title="Valider">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('comptabilite.paiements.rejeter', $paiement) }}" class="d-inline"
                                    onsubmit="return confirm('Rejeter cette déclaration ?')">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-danger" title="Rejeter">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('comptabilite.paiements.recu', $paiement) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                    <i class="bi bi-receipt"></i>
                                </a>
                                <a href="{{ route('comptabilite.paiements.edit', $paiement) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun paiement enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($paiements->hasPages())
        <div class="card-footer bg-white">
            {{ $paiements->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
