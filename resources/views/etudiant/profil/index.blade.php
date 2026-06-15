@extends('layouts.dashboard')

@section('title', 'Mon profil — SIGE UCAO')

@section('page-title', 'Mon profil')

@section('page-content')
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h3 class="h6 text-muted mb-3">Informations personnelles</h3>
                <table class="table table-sm mb-0">
                    <tr><th class="text-muted">Nom complet</th><td>{{ auth()->user()->nom_complet }}</td></tr>
                    <tr><th class="text-muted">Matricule</th><td><code>{{ $etudiant->matricule }}</code></td></tr>
                    <tr><th class="text-muted">Email</th><td>{{ auth()->user()->email ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Téléphone</th><td>{{ auth()->user()->telephone ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Adresse</th><td>{{ $etudiant->adresse ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Date de naissance</th><td>{{ optional($etudiant->date_naissance)->format('d/m/Y') ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Lieu de naissance</th><td>{{ $etudiant->lieu_naissance ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Filière</th><td>{{ $etudiant->filiere }}</td></tr>
                    <tr><th class="text-muted">Niveau</th><td>{{ $etudiant->niveau }}</td></tr>
                </table>
                <a href="{{ route('profile.password.edit') }}" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="bi bi-key me-1"></i>Modifier mon mot de passe
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h3 class="h6 text-muted mb-3">Situation académique et financière</h3>
                <table class="table table-sm mb-0">
                    <tr><th class="text-muted">Moyenne générale</th><td class="fw-bold">{{ $moyenne }} / 20</td></tr>
                    <tr>
                        <th class="text-muted">Solde restant</th>
                        <td class="fw-bold {{ $etudiant->solde > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($etudiant->solde, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Absences non justifiées</th>
                        <td class="fw-bold">{{ $etudiant->absencesNonJustifieesCount() }}</td>
                    </tr>
                </table>

                @if ($etudiant->enSituationRouge())
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Situation rouge :</strong> vous avez atteint {{ $etudiant->absencesNonJustifieesCount() }} absences non justifiées.
                        L'accès aux examens est bloqué tant que votre situation n'est pas régularisée.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h3 class="h6 text-muted mb-3">Coordonnées et contact d'urgence</h3>
                <p class="text-muted small">Le contact d'urgence sera prévenu par l'administration en cas de situation rouge (absences répétées) ou d'accident.</p>
                <form method="POST" action="{{ route('etudiant.profil.contact-urgence.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-2">
                        <label for="telephone" class="form-label">Mon téléphone</label>
                        <input type="text" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone', auth()->user()->telephone) }}">
                        @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="adresse" class="form-label">Mon adresse</label>
                        <input type="text" class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" value="{{ old('adresse', $etudiant->adresse) }}">
                        @error('adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label for="date_naissance" class="form-label">Date de naissance</label>
                            <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" id="date_naissance" name="date_naissance" value="{{ old('date_naissance', optional($etudiant->date_naissance)->format('Y-m-d')) }}">
                            @error('date_naissance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="lieu_naissance" class="form-label">Lieu de naissance</label>
                            <input type="text" class="form-control @error('lieu_naissance') is-invalid @enderror" id="lieu_naissance" name="lieu_naissance" value="{{ old('lieu_naissance', $etudiant->lieu_naissance) }}">
                            @error('lieu_naissance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="contact_urgence_nom" class="form-label">Nom du parent / tuteur</label>
                        <input type="text" class="form-control @error('contact_urgence_nom') is-invalid @enderror" id="contact_urgence_nom" name="contact_urgence_nom" value="{{ old('contact_urgence_nom', $etudiant->contact_urgence_nom) }}" required>
                        @error('contact_urgence_nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="contact_urgence_telephone" class="form-label">Téléphone du parent / tuteur</label>
                        <input type="text" class="form-control @error('contact_urgence_telephone') is-invalid @enderror" id="contact_urgence_telephone" name="contact_urgence_telephone" value="{{ old('contact_urgence_telephone', $etudiant->contact_urgence_telephone) }}" required>
                        @error('contact_urgence_telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-ucao btn-sm">
                        <i class="bi bi-save me-1"></i>Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
