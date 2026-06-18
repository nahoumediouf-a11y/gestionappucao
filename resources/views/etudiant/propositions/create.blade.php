@extends('layouts.dashboard')

@section('title', 'Proposer un projet — SIGE UCAO')
@section('page-title', 'Proposer un projet personnel')
@section('page-subtitle', 'Décrivez votre idée de projet. Elle sera examinée par l\'équipe pédagogique.')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('etudiant.propositions.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="titre" class="form-label fw-semibold">Titre du projet <span class="text-danger">*</span></label>
                    <input type="text" name="titre" id="titre" value="{{ old('titre') }}"
                        class="form-control @error('titre') is-invalid @enderror"
                        placeholder="Ex : Application de gestion de bibliothèque">
                    @error('titre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="matiere" class="form-label fw-semibold">Matière concernée <span class="text-muted">(optionnel)</span></label>
                    <input type="text" name="matiere" id="matiere" value="{{ old('matiere') }}"
                        class="form-control @error('matiere') is-invalid @enderror"
                        placeholder="Ex : Programmation Web">
                    @error('matiere')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" rows="6"
                        class="form-control @error('description') is-invalid @enderror"
                        placeholder="Décrivez votre projet en détail : objectifs, technologies envisagées, résultat attendu…">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Maximum 2000 caractères.</div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-send me-1"></i>Soumettre la proposition
                </button>
                <a href="{{ route('etudiant.propositions.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
