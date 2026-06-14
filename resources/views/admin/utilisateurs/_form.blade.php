@php
    $user = $user ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="nom" class="form-label">Nom</label>
        <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom', $user?->nom) }}" required>
        @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="prenom" class="form-label">Prénom</label>
        <input type="text" class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" value="{{ old('prenom', $user?->prenom) }}" required>
        @error('prenom') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="login" class="form-label">Login</label>
        <input type="text" class="form-control @error('login') is-invalid @enderror" id="login" name="login" value="{{ old('login', $user?->login) }}" required>
        @error('login') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user?->email) }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">Mot de passe {{ $user ? '(laisser vide pour ne pas changer)' : '' }}</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" {{ $user ? '' : 'required' }}>
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="role" class="form-label">Rôle</label>
        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required onchange="document.getElementById('etudiant-fields').classList.toggle('d-none', this.value !== 'etudiant')">
            @foreach ($roles as $role)
                <option value="{{ $role->value }}" {{ old('role', $user?->role?->value) === $role->value ? 'selected' : '' }}>
                    {{ $role->label() }}
                </option>
            @endforeach
        </select>
        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="statut" class="form-label">Statut</label>
        <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut" required>
            <option value="actif" {{ old('statut', $user?->statut ?? 'actif') === 'actif' ? 'selected' : '' }}>Actif</option>
            <option value="inactif" {{ old('statut', $user?->statut) === 'inactif' ? 'selected' : '' }}>Inactif</option>
        </select>
        @error('statut') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div id="etudiant-fields" class="row g-3 mt-1 {{ old('role', $user?->role?->value) === 'etudiant' ? '' : 'd-none' }}">
    <div class="col-12"><hr class="my-2"><h3 class="h6 text-muted">Informations étudiant</h3></div>

    <div class="col-md-3">
        <label for="matricule" class="form-label">Matricule</label>
        <input type="text" class="form-control @error('matricule') is-invalid @enderror" id="matricule" name="matricule" value="{{ old('matricule', $user?->etudiant?->matricule) }}" placeholder="ex : 1067604" pattern="\d{7}" maxlength="7">
        @error('matricule') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label for="niveau" class="form-label">Niveau</label>
        <input type="text" class="form-control @error('niveau') is-invalid @enderror" id="niveau" name="niveau" value="{{ old('niveau', $user?->etudiant?->niveau) }}" placeholder="Ex: L3">
        @error('niveau') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label for="filiere" class="form-label">Filière</label>
        <input type="text" class="form-control @error('filiere') is-invalid @enderror" id="filiere" name="filiere" value="{{ old('filiere', $user?->etudiant?->filiere) }}">
        @error('filiere') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label for="solde" class="form-label">Solde restant (FCFA)</label>
        <input type="number" step="0.01" min="0" class="form-control @error('solde') is-invalid @enderror" id="solde" name="solde" value="{{ old('solde', $user?->etudiant?->solde) }}">
        @error('solde') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
