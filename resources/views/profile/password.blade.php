@extends('layouts.dashboard')

@section('title', 'Modifier le mot de passe — Recouvrement UCAO')

@section('page-title', 'Modifier mon mot de passe')

@section('page-content')
<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="mot_de_passe_actuel" class="form-label">Mot de passe actuel</label>
                        <input type="password" class="form-control @error('mot_de_passe_actuel') is-invalid @enderror" id="mot_de_passe_actuel" name="mot_de_passe_actuel" required>
                        @error('mot_de_passe_actuel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control @error('mot_de_passe') is-invalid @enderror" id="mot_de_passe" name="mot_de_passe" required>
                        @error('mot_de_passe')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="mot_de_passe_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-ucao">
                        <i class="bi bi-check-circle me-1"></i>Mettre à jour
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
