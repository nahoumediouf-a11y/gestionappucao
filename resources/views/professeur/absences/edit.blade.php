@extends('layouts.dashboard')

@section('title', 'Modifier une absence — SIGE UCAO')

@section('page-title', 'Modifier une absence')
@section('page-subtitle', $absence->etudiant->user->nom_complet.' ('.$absence->etudiant->matricule.')')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('professeur.absences.update', $absence) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="matiere" class="form-label">Matière</label>
                    <input type="text" name="matiere" id="matiere" value="{{ old('matiere', $absence->matiere) }}"
                        class="form-control @error('matiere') is-invalid @enderror">
                    @error('matiere')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" name="date" id="date" value="{{ old('date', \Illuminate\Support\Carbon::parse($absence->date)->format('Y-m-d')) }}"
                        class="form-control @error('date') is-invalid @enderror">
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="justifiee" id="justifiee" value="1" class="form-check-input"
                            @checked(old('justifiee', $absence->justifiee))>
                        <label for="justifiee" class="form-check-label">Absence justifiée</label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-check-circle me-1"></i>Enregistrer
                </button>
                <a href="{{ route('professeur.absences.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
