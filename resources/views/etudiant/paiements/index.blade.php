@extends('layouts.dashboard')

@section('title', 'Suivi de paiement & scolarité — SIGE UCAO')

@section('page-title', 'Suivi de paiement')
@section('page-subtitle', 'Consultez votre situation financière et payez votre scolarité.')

@section('page-content')

{{-- ===== CARTE SCOLARITÉ ===== --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-{{ $etudiant->solde > 0 ? 'danger' : 'success' }} bg-opacity-10">
                        <i class="bi bi-mortarboard fs-3 text-{{ $etudiant->solde > 0 ? 'danger' : 'success' }}"></i>
                    </div>
                    <div>
                        <div class="text-muted small mb-1">Frais de scolarité — Solde restant</div>
                        <div class="fs-2 fw-bold {{ $etudiant->solde > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($etudiant->solde, 0, ',', ' ') }} FCFA
                        </div>
                        @if ($etudiant->solde <= 0)
                            <span class="badge bg-success mt-1"><i class="bi bi-check-circle me-1"></i>Scolarité à jour</span>
                        @else
                            <span class="badge bg-danger mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Solde impayé</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                @php
                    $totalPaye = $paiements->where('statut', 'valide')->sum('montant');
                    $enAttente = $paiements->where('statut', 'en_attente_validation')->count();
                @endphp
                <div class="text-muted small mb-1">Déjà réglé</div>
                <div class="fs-5 fw-semibold text-success">{{ number_format($totalPaye, 0, ',', ' ') }} FCFA</div>
                @if ($enAttente > 0)
                    <div class="text-warning small mt-1">
                        <i class="bi bi-clock me-1"></i>{{ $enAttente }} déclaration(s) en attente de validation
                    </div>
                @endif
                @if ($etudiant->solde > 0)
                    <a href="#payer-scolarite" class="btn btn-ucao mt-2">
                        <i class="bi bi-credit-card me-1"></i>Payer ma scolarité
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===== FORMULAIRE PAYER MA SCOLARITÉ ===== --}}
@if ($etudiant->solde > 0)
<div class="card border-0 shadow-sm mb-4" id="payer-scolarite">
    <div class="card-header bg-white py-3">
        <h3 class="h6 mb-0 fw-bold"><i class="bi bi-credit-card me-2 text-primary"></i>Payer ma scolarité</h3>
        <div class="text-muted small mt-1">Saisissez les informations de votre paiement. Le service comptable le validera sous 24h.</div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('etudiant.paiements.store') }}">
            @csrf
            <div class="row g-3">

                {{-- Montant --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Montant à payer (FCFA) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                        <input type="number" name="montant" min="1" max="{{ (int) $etudiant->solde }}"
                            class="form-control @error('montant') is-invalid @enderror"
                            value="{{ old('montant', (int) $etudiant->solde) }}" required>
                        @error('montant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-text">Max : {{ number_format($etudiant->solde, 0, ',', ' ') }} FCFA</div>
                </div>

                {{-- Mode de paiement --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mode de paiement <span class="text-danger">*</span></label>
                    <select name="mode_paiement" id="mode_paiement_etu"
                        class="form-select @error('mode_paiement') is-invalid @enderror"
                        required onchange="ucaoModeChange(this.value)">
                        @foreach (\App\Models\Paiement::MODES as $v => $l)
                            <option value="{{ $v }}" {{ old('mode_paiement') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('mode_paiement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- N° téléphone (Wave / Orange Money) --}}
                <div class="col-md-4" id="champ-tel-etu" style="display:none">
                    <label class="form-label fw-semibold">N° téléphone expéditeur</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-phone"></i></span>
                        <input type="tel" name="numero_mobile"
                            class="form-control @error('numero_mobile') is-invalid @enderror"
                            value="{{ old('numero_mobile') }}" placeholder="77 000 00 00">
                        @error('numero_mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-text">Numéro depuis lequel vous avez envoyé.</div>
                </div>

                {{-- Référence transaction --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Référence / N° de transaction <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-hash"></i></span>
                        <input type="text" name="reference"
                            class="form-control @error('reference') is-invalid @enderror"
                            value="{{ old('reference') }}" placeholder="Numéro de transaction" required>
                        @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-text">Copié depuis votre SMS / reçu Wave, Orange Money, Visa…</div>
                </div>

                {{-- Note --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Note (optionnel)</label>
                    <input type="text" name="note_etudiant"
                        class="form-control @error('note_etudiant') is-invalid @enderror"
                        value="{{ old('note_etudiant') }}" placeholder="Commentaire sur ce paiement">
                    @error('note_etudiant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Aide par mode --}}
                <div class="col-12" id="aide-mode" style="display:none">
                    <div id="aide-wave" class="alert alert-info py-2 small mb-0" style="display:none">
                        <strong>Wave :</strong> Envoyez le montant au <strong>numéro de caisse de l'UCAO</strong>, puis renseignez ici le numéro de transaction reçu par SMS.
                    </div>
                    <div id="aide-orange" class="alert alert-warning py-2 small mb-0" style="display:none">
                        <strong>Orange Money :</strong> Effectuez le transfert via l'application OM, puis saisissez ici le code de transaction reçu.
                    </div>
                    <div id="aide-visa" class="alert alert-secondary py-2 small mb-0" style="display:none">
                        <strong>Carte Visa / Bancaire :</strong> Rapprochez-vous du service comptable ou renseignez le numéro d'autorisation de votre banque.
                    </div>
                </div>

                <div class="col-12 border-top pt-3">
                    <button type="submit" class="btn btn-ucao">
                        <i class="bi bi-send me-1"></i>Envoyer ma déclaration de paiement
                    </button>
                    <span class="text-muted small ms-3">
                        <i class="bi bi-shield-check me-1 text-success"></i>Votre paiement sera validé par le service comptable sous 24h.
                    </span>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ===== HISTORIQUE ===== --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <strong><i class="bi bi-clock-history me-2"></i>Historique des paiements</strong>
    </div>
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
                        <td class="fw-semibold">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $paiement->modeLabel() }}</td>
                        <td><span class="badge bg-{{ $s['color'] }}">{{ $s['label'] }}</span></td>
                        <td class="text-end">
                            @if ($paiement->statut === 'valide')
                                <a href="{{ route('etudiant.paiements.recu', $paiement) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download me-1"></i>Reçu
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun paiement enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== ENGAGEMENTS ===== --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <strong><i class="bi bi-calendar-check me-2"></i>Engagements de paiement</strong>
    </div>
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
                        <td class="fw-semibold">{{ number_format($engagement->montant, 0, ',', ' ') }} FCFA</td>
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
function ucaoModeChange(mode) {
    const mobiles = ['wave', 'orange_money', 'mobile_money'];
    document.getElementById('champ-tel-etu').style.display = mobiles.includes(mode) ? '' : 'none';

    const aide = document.getElementById('aide-mode');
    const aideWave   = document.getElementById('aide-wave');
    const aideOrange = document.getElementById('aide-orange');
    const aideVisa   = document.getElementById('aide-visa');

    aideWave.style.display   = mode === 'wave'         ? '' : 'none';
    aideOrange.style.display = mode === 'orange_money' ? '' : 'none';
    aideVisa.style.display   = mode === 'visa'         ? '' : 'none';

    aide.style.display = ['wave', 'orange_money', 'visa'].includes(mode) ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('mode_paiement_etu');
    if (sel) ucaoModeChange(sel.value);
});
</script>
@endsection
