@extends('layouts.dashboard')

@section('title', 'Suivi de paiement — SIGE UCAO')

@section('page-title', 'Suivi de paiement')

@section('page-content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Solde restant à payer</div>
                <div class="fs-3 fw-bold {{ $etudiant->solde > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($etudiant->solde, 0, ',', ' ') }} FCFA
                </div>
            </div>
        </div>
    </div>
    @if ($etudiant->solde > 0)
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-send me-1 text-primary"></i>Déclarer un paiement effectué
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('etudiant.paiements.store') }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label small">Montant (FCFA)</label>
                            <input type="number" name="montant" min="1" max="{{ $etudiant->solde }}"
                                class="form-control form-control-sm @error('montant') is-invalid @enderror"
                                value="{{ old('montant') }}" placeholder="Ex : 50000" required>
                            @error('montant') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label small">Mode de paiement</label>
                            <select name="mode_paiement" class="form-select form-select-sm @error('mode_paiement') is-invalid @enderror"
                                required onchange="ucaoToggleNumMobile(this.value)">
                                @foreach (\App\Models\Paiement::MODES as $v => $l)
                                    <option value="{{ $v }}" {{ old('mode_paiement') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                            @error('mode_paiement') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-6 col-md-3" id="champ-tel-etudiant">
                            <label class="form-label small">N° téléphone expéditeur</label>
                            <input type="tel" name="numero_mobile"
                                class="form-control form-control-sm @error('numero_mobile') is-invalid @enderror"
                                value="{{ old('numero_mobile') }}" placeholder="77 000 00 00">
                            @error('numero_mobile') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label small">Référence / N° transaction</label>
                            <input type="text" name="reference"
                                class="form-control form-control-sm @error('reference') is-invalid @enderror"
                                value="{{ old('reference') }}" placeholder="Ex : TXN-XXXXXXX" required>
                            @error('reference') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Note / remarque (optionnel)</label>
                            <input type="text" name="note_etudiant"
                                class="form-control form-control-sm @error('note_etudiant') is-invalid @enderror"
                                value="{{ old('note_etudiant') }}" placeholder="Ex : Paiement de la tranche 1">
                            @error('note_etudiant') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-ucao btn-sm">
                                <i class="bi bi-send me-1"></i>Envoyer la déclaration
                            </button>
                            <span class="text-muted small ms-2">Le service comptable validera votre paiement.</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><strong>Historique des paiements / Reçus</strong></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Mode</th>
                    <th>Statut</th>
                    <th class="text-end">Reçu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paiements as $paiement)
                    @php $s = \App\Models\Paiement::STATUTS[$paiement->statut] ?? ['label' => $paiement->statut, 'color' => 'secondary']; @endphp
                    <tr>
                        <td><code>{{ $paiement->reference }}</code></td>
                        <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                        <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $paiement->modeLabel() }}</td>
                        <td><span class="badge bg-{{ $s['color'] }}">{{ $s['label'] }}</span></td>
                        <td class="text-end">
                            @if ($paiement->statut === 'valide')
                                <a href="{{ route('etudiant.paiements.recu', $paiement) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download me-1"></i>Reçu
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucun paiement enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><strong>Engagements de paiement</strong></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Montant</th>
                    <th>Échéance</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($engagements as $engagement)
                    <tr>
                        <td>{{ number_format($engagement->montant, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $engagement->echeance->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-{{ match($engagement->statut) {
                                'honore' => 'success',
                                'relance' => 'warning',
                                'annule' => 'secondary',
                                default => 'info',
                            } }}">
                                {{ ucfirst($engagement->statut) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Aucun engagement en cours.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function ucaoToggleNumMobile(mode) {
    const mobiles = ['wave', 'orange_money', 'mobile_money'];
    document.getElementById('champ-tel-etudiant').style.display = mobiles.includes(mode) ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.querySelector('select[name="mode_paiement"]');
    if (sel) ucaoToggleNumMobile(sel.value);
});
</script>
@endsection
