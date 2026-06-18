@extends('layouts.app')

@section('title', 'Créer un compte étudiant — SIGE UCAO')

@section('content')
<div class="auth-wrapper position-relative">
    <span class="theme-toggle fs-4 text-white position-absolute top-0 end-0 m-3" onclick="ucaoToggleTheme()" title="Changer de thème">
        <i id="ucao-theme-icon" class="bi bi-moon-stars"></i>
    </span>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <a href="{{ route('login', ['espace' => 'etudiant']) }}" class="d-inline-flex align-items-center gap-2 text-white text-decoration-none mb-3 opacity-75">
                    <i class="bi bi-arrow-left"></i> Retour à la connexion
                </a>
                <div class="card auth-card">
                    <div class="row g-0">
                        <div class="col-md-5 auth-brand d-flex flex-column justify-content-center">
                            <div class="px-2">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-person-badge fs-2"></i>
                                    <span class="fs-5 fw-semibold">UCAO Saint Michel</span>
                                </div>
                                <h1 class="h3 fw-bold mb-3">Créer mon compte étudiant</h1>
                                <p class="mb-4 opacity-75">
                                    Inscrivez-vous en quelques instants pour accéder à votre espace :
                                    notes, bulletin, emploi du temps, absences et paiements.
                                </p>
                                <ul class="list-unstyled small opacity-90 mb-0">
                                    <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Un matricule vous est attribué automatiquement</li>
                                    <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Connexion immédiate après inscription</li>
                                    <li><i class="bi bi-check-circle me-2"></i>Vos informations restent modifiables ensuite</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-7 auth-form-panel p-4 p-md-5">
                            <h2 class="h4 mb-1">Inscription étudiant</h2>
                            <p class="text-muted mb-4">Créez votre compte pour accéder à l'espace étudiant</p>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <form method="POST" action="{{ route('register') }}" novalidate>
                                @csrf

                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label for="prenom" class="form-label">Prénom</label>
                                        <input
                                            type="text"
                                            class="form-control @error('prenom') is-invalid @enderror"
                                            id="prenom"
                                            name="prenom"
                                            value="{{ old('prenom') }}"
                                            required
                                            autofocus
                                        >
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label for="nom" class="form-label">Nom</label>
                                        <input
                                            type="text"
                                            class="form-control @error('nom') is-invalid @enderror"
                                            id="nom"
                                            name="nom"
                                            value="{{ old('nom') }}"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="login" class="form-label">Login</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input
                                            type="text"
                                            class="form-control @error('login') is-invalid @enderror"
                                            id="login"
                                            name="login"
                                            value="{{ old('login') }}"
                                            placeholder="Votre identifiant de connexion"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-muted">(optionnel)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input
                                            type="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            id="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            placeholder="Votre adresse email"
                                        >
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label for="filiere" class="form-label">Filière</label>
                                        <select class="form-select @error('filiere') is-invalid @enderror" id="filiere" name="filiere" required>
                                            <option value="" disabled {{ old('filiere') ? '' : 'selected' }}>Choisir...</option>
                                            @foreach (\App\Models\Etudiant::FILIERES as $sigle => $libelle)
                                                <option value="{{ $sigle }}" {{ old('filiere') === $sigle ? 'selected' : '' }}>{{ $sigle }} — {{ $libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label for="niveau" class="form-label">Classe</label>
                                        <select class="form-select @error('niveau') is-invalid @enderror" id="niveau" name="niveau" required>
                                            <option value="" disabled {{ old('niveau') ? '' : 'selected' }}>Choisir...</option>
                                            @foreach (\App\Models\Etudiant::NIVEAUX as $niveau)
                                                <option value="{{ $niveau }}" {{ old('niveau') === $niveau ? 'selected' : '' }}>{{ $niveau }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label for="password" class="form-label">Mot de passe</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                                            <input
                                                type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                id="password"
                                                name="password"
                                                placeholder="8 caractères min., lettres + chiffres"
                                                required
                                            >
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                            <input
                                                type="password"
                                                class="form-control"
                                                id="password_confirmation"
                                                name="password_confirmation"
                                                placeholder="••••••••"
                                                required
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="captcha" class="form-label">Vérification : combien font {{ $captcha['question'] }} ?</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                        <input
                                            type="number"
                                            class="form-control @error('captcha') is-invalid @enderror"
                                            id="captcha"
                                            name="captcha"
                                            placeholder="Votre réponse"
                                            required
                                        >
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-ucao w-100 py-2 mt-2">
                                    <i class="bi bi-person-plus me-2"></i>Créer mon compte
                                </button>
                            </form>

                            <hr class="my-4">

                            <p class="small text-muted mb-0">
                                Vous avez déjà un compte ?
                                <a href="{{ route('login', ['espace' => 'etudiant']) }}">Se connecter</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
