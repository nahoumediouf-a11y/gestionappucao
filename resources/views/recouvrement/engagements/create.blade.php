@extends('layouts.dashboard')

@section('title', 'Créer un engagement — Recouvrement UCAO')

@section('page-title', 'Créer un engagement de paiement')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('recouvrement.engagements.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="etudiant_id" class="form-label">Étudiant débiteur</label>
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
                    <label for="montant" class="form-label">Montant engagé (FCFA)</label>
                    <input type="number" step="0.01" min="1" class="form-control @error('montant') is-invalid @enderror" id="montant" name="montant" value="{{ old('montant') }}" required>
                    @error('montant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="date" class="form-label">Date de l'engagement</label>
                    <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', now()->toDateString()) }}" required>
                    @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="echeance" class="form-label">Échéance</label>
                    <input type="date" class="form-control @error('echeance') is-invalid @enderror" id="echeance" name="echeance" value="{{ old('echeance') }}" required>
                    @error('echeance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-check-circle me-1"></i>Créer l'engagement
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
