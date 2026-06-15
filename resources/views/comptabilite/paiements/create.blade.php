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
                    <select class="form-select @error('mode_paiement') is-invalid @enderror" id="mode_paiement" name="mode_paiement" required>
                        <option value="especes" {{ old('mode_paiement') === 'especes' ? 'selected' : '' }}>Espèces</option>
                        <option value="virement" {{ old('mode_paiement') === 'virement' ? 'selected' : '' }}>Virement</option>
                        <option value="cheque" {{ old('mode_paiement') === 'cheque' ? 'selected' : '' }}>Chèque</option>
                        <option value="mobile_money" {{ old('mode_paiement') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    </select>
                    @error('mode_paiement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="reference" class="form-label">Référence</label>
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
@endsection
