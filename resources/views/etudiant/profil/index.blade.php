@extends('layouts.dashboard')

@section('title', 'Mon profil — Recouvrement UCAO')

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
</div>
@endsection
