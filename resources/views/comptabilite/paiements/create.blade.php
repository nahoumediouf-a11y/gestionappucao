@extends('layouts.dashboard')

@section('title', 'Enregistrer un paiement — SIGE UCAO')

@section('page-title', 'Enregistrer un paiement')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('comptabilite.paiements.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="etudiant_id" class="form-label">Étudiant (débiteur)</label>
                    <select class="form-select @error('etudiant_id') is-invalid @enderror" id="etudiant_id" name="etudiant_id" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach ($etudiants as $etudiant)
                            <option value="{{ $etudiant->id }}" {{ old('etudiant_id') == $etudiant->id ? 'selected' : '' }}>
                                {{ $etudiant->matricule }} — {{ $etudiant->user->nom_complet }} (solde: {{ number_format($etudiant->solde, 0, ',', ' ') }} FCFA)
                            </option>
                        @endforeach
                    </select>
                    @error('etudiant_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="montant" class="form-label">Montant (FCFA)</label>
                    <input type="number" step="0.01" min="1" class="form-control @error('montant') is-invalid @enderror" id="montant" name="montant" value="{{ old('montant') }}" required>
                    @error('montant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="date_paiement" class="form-label">Date de paiement</label>
                    <input type="date" class="form-control @error('date_paiement') is-invalid @enderror" id="date_paiement" name="date_paiement" value="{{ old('date_paiement', now()->toDateString()) }}" required>
                    @error('date_paiement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="mode_paiement" class="form-label">Mode de paiement</label>
                    <select class="form-select @error('mode_paiement') is-invalid @enderror" id="mode_paiement" name="mode_paiement" required onchange="ucaoToggleNumeroMobile(this.value)">
                        @foreach (\App\Models\Paiement::MODES as $value => $label)
                            <option value="{{ $value }}" {{ old('mode_paiement') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('mode_paiement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4" id="champ-numero-mobile" style="display:none">
                    <label for="numero_mobile" class="form-label">Numéro de téléphone</label>
                    <input type="tel" class="form-control" id="numero_mobile" name="numero_mobile"
                        value="{{ old('numero_mobile') }}" placeholder="Ex : 77 000 00 00">
                    <div class="form-text">Numéro ayant effectué l'envoi.</div>
                </div>

                <div class="col-md-4">
                    <label for="reference" class="form-label">Référence / N° transaction</label>
                    <input type="text" class="form-control @error('reference') is-invalid @enderror" id="reference" name="reference" value="{{ old('reference', 'REC-'.now()->format('Y').'-'.strtoupper(substr(uniqid(), -6))) }}" required>
                    @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-check-circle me-1"></i>Enregistrer le paiement
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function ucaoToggleNumeroMobile(mode) {
    const mobiles = ['wave', 'orange_money', 'mobile_money'];
    const champ = document.getElementById('champ-numero-mobile');
    const input = document.getElementById('numero_mobile');
    const afficher = mobiles.includes(mode);
    champ.style.display = afficher ? '' : 'none';
    input.required = afficher;
}
document.addEventListener('DOMContentLoaded', function () {
    ucaoToggleNumeroMobile(document.getElementById('mode_paiement').value);
});
</script>
@endsection
