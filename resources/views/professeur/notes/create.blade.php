@extends('layouts.dashboard')

@section('title', 'Saisir une note — Recouvrement UCAO')

@section('page-title', 'Saisir une note')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('professeur.notes.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="etudiant_id" class="form-label">Étudiant</label>
                    <select name="etudiant_id" id="etudiant_id" class="form-select @error('etudiant_id') is-invalid @enderror">
                        <option value="">-- Sélectionner --</option>
                        @foreach ($etudiants as $etudiant)
                            <option value="{{ $etudiant->id }}" @selected(old('etudiant_id') == $etudiant->id)>
                                {{ $etudiant->user->nom_complet }} ({{ $etudiant->matricule }}) — {{ $etudiant->filiere }} {{ $etudiant->niveau }}
                            </option>
                        @endforeach
                    </select>
                    @error('etudiant_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="matiere" class="form-label">Matière</label>
                    <input type="text" name="matiere" id="matiere" value="{{ old('matiere') }}"
                        class="form-control @error('matiere') is-invalid @enderror">
                    @error('matiere')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="session" class="form-label">Session</label>
                    <input type="text" name="session" id="session" value="{{ old('session') }}"
                        class="form-control @error('session') is-invalid @enderror" placeholder="Ex: Semestre 1">
                    @error('session')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="valeur" class="form-label">Note / 20</label>
                    <input type="number" step="0.01" min="0" max="20" name="valeur" id="valeur" value="{{ old('valeur') }}"
                        class="form-control @error('valeur') is-invalid @enderror">
                    @error('valeur')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-check-circle me-1"></i>Enregistrer
                </button>
                <a href="{{ route('professeur.notes.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
