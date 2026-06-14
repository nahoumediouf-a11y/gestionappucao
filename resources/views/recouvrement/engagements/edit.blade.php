@extends('layouts.dashboard')

@section('title', 'Modifier un engagement — Recouvrement UCAO')

@section('page-title', 'Modifier l\'engagement')
@section('page-subtitle', 'Étudiant : '.$engagement->etudiant->user->nom_complet.' ('.$engagement->etudiant->matricule.')')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('recouvrement.engagements.update', $engagement) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="montant" class="form-label">Montant (FCFA)</label>
                    <input type="number" step="0.01" min="1" class="form-control @error('montant') is-invalid @enderror" id="montant" name="montant" value="{{ old('montant', $engagement->montant) }}" required>
                    @error('montant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="echeance" class="form-label">Échéance</label>
                    <input type="date" class="form-control @error('echeance') is-invalid @enderror" id="echeance" name="echeance" value="{{ old('echeance', $engagement->echeance->toDateString()) }}" required>
                    @error('echeance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut" required>
                        @foreach (['en_attente' => 'En attente', 'relance' => 'Relancé', 'honore' => 'Honoré', 'annule' => 'Annulé'] as $value => $label)
                            <option value="{{ $value }}" {{ old('statut', $engagement->statut) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
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
