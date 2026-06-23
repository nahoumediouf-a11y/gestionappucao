@extends('layouts.dashboard')

@section('title', 'Mon compte — SIGE UCAO')

@section('page-title', 'Mon compte')

@section('page-content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        @include('partials._identite', ['identite' => $user, 'sousTitre' => '@'.$user->login])
    </div>
</div>

<div class="row g-3">
    {{-- Informations personnelles (tous rôles) --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h3 class="h6 text-muted mb-3"><i class="bi bi-person-vcard me-1"></i>Informations personnelles</h3>
                <form method="POST" action="{{ route('compte.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @include('partials._identite', ['identite' => $user, 'taille' => 'md', 'sousTitre' => null])
                    </div>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo de profil</label>
                        <input type="file" name="photo" id="photo" accept="image/*"
                            class="form-control @error('photo') is-invalid @enderror">
                        <div class="form-text">Toute image (JPG, PNG, GIF, WebP…), 8 Mo max.</div>
                        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if ($user->photoUrl())
                            <div class="form-check mt-2">
                                <input type="checkbox" name="supprimer_photo" value="1" id="supprimer_photo" class="form-check-input">
                                <label for="supprimer_photo" class="form-check-label small">Retirer ma photo actuelle</label>
                            </div>
                        @endif
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" name="prenom" id="prenom" value="{{ old('prenom', $user->prenom) }}"
                                class="form-control @error('prenom') is-invalid @enderror">
                            @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" name="nom" id="nom" value="{{ old('nom', $user->nom) }}"
                                class="form-control @error('nom') is-invalid @enderror">
                            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                class="form-control @error('email') is-invalid @enderror">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="text" name="telephone" id="telephone" value="{{ old('telephone', $user->telephone) }}"
                                class="form-control @error('telephone') is-invalid @enderror">
                            @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Login</label>
                            <input type="text" class="form-control" value="{{ $user->login }}" disabled>
                            <div class="form-text">Le login ne peut être modifié que par l'administration.</div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-ucao mt-3"><i class="bi bi-save me-1"></i>Enregistrer</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Mot de passe (tous rôles) --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h3 class="h6 text-muted mb-3"><i class="bi bi-key me-1"></i>Changer mon mot de passe</h3>
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-2">
                        <label for="mot_de_passe_actuel" class="form-label">Mot de passe actuel</label>
                        <input type="password" name="mot_de_passe_actuel" id="mot_de_passe_actuel"
                            class="form-control @error('mot_de_passe_actuel') is-invalid @enderror">
                        @error('mot_de_passe_actuel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label for="mot_de_passe" class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="mot_de_passe" id="mot_de_passe"
                            class="form-control @error('mot_de_passe') is-invalid @enderror">
                        @error('mot_de_passe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="mot_de_passe_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="mot_de_passe_confirmation" id="mot_de_passe_confirmation" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-shield-lock me-1"></i>Mettre à jour</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Bloc spécifique étudiant --}}
    @if ($etudiant)
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h3 class="h6 text-muted mb-3"><i class="bi bi-mortarboard me-1"></i>Ma situation</h3>
                    <table class="table table-sm mb-3">
                        <tr><th class="text-muted">Matricule</th><td><code>{{ $etudiant->matricule }}</code></td></tr>
                        <tr><th class="text-muted">Filière / Niveau</th><td>{{ $etudiant->filiere }} {{ $etudiant->niveau }}</td></tr>
                        <tr><th class="text-muted">Moyenne générale</th><td class="fw-bold">{{ $moyenne }} / 20</td></tr>
                        <tr>
                            <th class="text-muted">Solde restant</th>
                            <td class="fw-bold {{ $etudiant->solde > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($etudiant->solde, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    </table>
                    @if ($etudiant->solde > 0)
                        <a href="{{ route('etudiant.paiements.index') }}#payer-scolarite" class="btn btn-danger btn-sm w-100">
                            <i class="bi bi-credit-card-fill me-1"></i>Payer ma scolarité
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h3 class="h6 text-muted mb-3"><i class="bi bi-person-heart me-1"></i>Contact d'urgence</h3>
                    <form method="POST" action="{{ route('etudiant.profil.contact-urgence.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="telephone" value="{{ $user->telephone }}">
                        <input type="hidden" name="adresse" value="{{ $etudiant->adresse }}">
                        <input type="hidden" name="date_naissance" value="{{ optional($etudiant->date_naissance)->format('Y-m-d') }}">
                        <input type="hidden" name="lieu_naissance" value="{{ $etudiant->lieu_naissance }}">
                        <div class="mb-2">
                            <label for="contact_urgence_nom" class="form-label">Nom du parent / tuteur</label>
                            <input type="text" name="contact_urgence_nom" id="contact_urgence_nom" required
                                value="{{ old('contact_urgence_nom', $etudiant->contact_urgence_nom) }}"
                                class="form-control @error('contact_urgence_nom') is-invalid @enderror">
                            @error('contact_urgence_nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label for="contact_urgence_telephone" class="form-label">Téléphone du parent / tuteur</label>
                            <input type="text" name="contact_urgence_telephone" id="contact_urgence_telephone" required
                                value="{{ old('contact_urgence_telephone', $etudiant->contact_urgence_telephone) }}"
                                class="form-control @error('contact_urgence_telephone') is-invalid @enderror">
                            @error('contact_urgence_telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="email_parent" class="form-label">Email du parent / tuteur</label>
                            <input type="email" name="email_parent" id="email_parent"
                                value="{{ old('email_parent', $etudiant->email_parent) }}"
                                class="form-control @error('email_parent') is-invalid @enderror">
                            @error('email_parent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-ucao btn-sm"><i class="bi bi-save me-1"></i>Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
