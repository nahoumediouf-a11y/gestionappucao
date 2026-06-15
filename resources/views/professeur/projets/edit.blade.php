@extends('layouts.dashboard')

@section('title', 'Modifier — SIGE UCAO')

@section('page-title', 'Modifier')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('professeur.projets.update', $projet) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-3">
                    <label for="type" class="form-label">Type</label>
                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                        @foreach (\App\Models\Projet::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $projet->type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text" name="titre" id="titre" value="{{ old('titre', $projet->titre) }}"
                        class="form-control @error('titre') is-invalid @enderror">
                    @error('titre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="filiere" class="form-label">Filière</label>
                    <input type="text" name="filiere" id="filiere" value="{{ old('filiere', $projet->filiere) }}"
                        class="form-control @error('filiere') is-invalid @enderror">
                    @error('filiere')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="niveau" class="form-label">Niveau</label>
                    <input type="text" name="niveau" id="niveau" value="{{ old('niveau', $projet->niveau) }}"
                        class="form-control @error('niveau') is-invalid @enderror">
                    @error('niveau')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="matiere" class="form-label">Matière</label>
                    <input type="text" name="matiere" id="matiere" value="{{ old('matiere', $projet->matiere) }}"
                        class="form-control @error('matiere') is-invalid @enderror">
                    @error('matiere')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="date_limite" class="form-label">Date limite / date de l'examen</label>
                    <input type="date" name="date_limite" id="date_limite" value="{{ old('date_limite', $projet->date_limite->format('Y-m-d')) }}"
                        class="form-control @error('date_limite') is-invalid @enderror">
                    @error('date_limite')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description / consignes</label>
                    <textarea name="description" id="description" rows="4"
                        class="form-control @error('description') is-invalid @enderror">{{ old('description', $projet->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                </button>
                <a href="{{ route('professeur.projets.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
