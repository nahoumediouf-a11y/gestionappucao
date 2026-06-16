@extends('layouts.dashboard')

@section('title', 'Modifier un paiement — SIGE UCAO')

@section('page-title', 'Modifier le paiement '.$paiement->reference)
@section('page-subtitle', 'Étudiant : '.$paiement->etudiant->user->nom_complet.' ('.$paiement->etudiant->matricule.')')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('comptabilite.paiements.update', $paiement) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-3">
                    <label for="montant" class="form-label">Montant (FCFA)</label>
                    <input type="number" step="0.01" min="0" class="form-control @error('montant') is-invalid @enderror" id="montant" name="montant" value="{{ old('montant', $paiement->montant) }}" required>
                    @error('montant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="date_paiement" class="form-label">Date de paiement</label>
                    <input type="date" class="form-control @error('date_paiement') is-invalid @enderror" id="date_paiement" name="date_paiement" value="{{ old('date_paiement', $paiement->date_paiement->toDateString()) }}" required>
                    @error('date_paiement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="mode_paiement" class="form-label">Mode de paiement</label>
                    <select class="form-select @error('mode_paiement') is-invalid @enderror" id="mode_paiement" name="mode_paiement" required>
                        @foreach (\App\Models\Paiement::MODES as $value => $label)
                            <option value="{{ $value }}" {{ old('mode_paiement', $paiement->mode_paiement) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('mode_paiement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut" required>
                        <option value="valide" {{ old('statut', $paiement->statut) === 'valide' ? 'selected' : '' }}>Validé</option>
                        <option value="annule" {{ old('statut', $paiement->statut) === 'annule' ? 'selected' : '' }}>Annulé</option>
                    </select>
                    @error('statut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
