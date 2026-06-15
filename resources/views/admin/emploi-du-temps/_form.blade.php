@php
    $creneau = $creneau ?? null;
    $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="filiere" class="form-label">Filière</label>
        <input type="text" class="form-control @error('filiere') is-invalid @enderror" id="filiere" name="filiere" value="{{ old('filiere', $creneau?->filiere) }}" required>
        @error('filiere') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="niveau" class="form-label">Niveau</label>
        <input type="text" class="form-control @error('niveau') is-invalid @enderror" id="niveau" name="niveau" value="{{ old('niveau', $creneau?->niveau) }}" placeholder="Ex: L3" required>
        @error('niveau') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="matiere" class="form-label">Matière</label>
        <input type="text" class="form-control @error('matiere') is-invalid @enderror" id="matiere" name="matiere" value="{{ old('matiere', $creneau?->matiere) }}" required>
        @error('matiere') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="professeur_id" class="form-label">Professeur</label>
        <select class="form-select @error('professeur_id') is-invalid @enderror" id="professeur_id" name="professeur_id">
            <option value="">— Aucun —</option>
            @foreach ($professeurs as $professeur)
                <option value="{{ $professeur->id }}" {{ (string) old('professeur_id', $creneau?->professeur_id) === (string) $professeur->id ? 'selected' : '' }}>
                    {{ $professeur->nom_complet }}
                </option>
            @endforeach
        </select>
        @error('professeur_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="jour" class="form-label">Jour</label>
        <select class="form-select @error('jour') is-invalid @enderror" id="jour" name="jour" required>
            @foreach ($jours as $jour)
                <option value="{{ $jour }}" {{ old('jour', $creneau?->jour) === $jour ? 'selected' : '' }}>{{ $jour }}</option>
            @endforeach
        </select>
        @error('jour') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="heure_debut" class="form-label">Heure de début</label>
        <input type="time" class="form-control @error('heure_debut') is-invalid @enderror" id="heure_debut" name="heure_debut" value="{{ old('heure_debut', optional($creneau?->heure_debut ? \Illuminate\Support\Carbon::parse($creneau->heure_debut) : null)->format('H:i')) }}" required>
        @error('heure_debut') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="heure_fin" class="form-label">Heure de fin</label>
        <input type="time" class="form-control @error('heure_fin') is-invalid @enderror" id="heure_fin" name="heure_fin" value="{{ old('heure_fin', optional($creneau?->heure_fin ? \Illuminate\Support\Carbon::parse($creneau->heure_fin) : null)->format('H:i')) }}" required>
        @error('heure_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="salle" class="form-label">Salle</label>
        <input type="text" class="form-control @error('salle') is-invalid @enderror" id="salle" name="salle" value="{{ old('salle', $creneau?->salle) }}" placeholder="Ex: 2.1" required>
        @error('salle') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if ($creneau)
            <div class="form-text">Si vous changez la salle, les étudiants de {{ $creneau->filiere }} {{ $creneau->niveau }} et le professeur seront notifiés automatiquement.</div>
        @endif
    </div>
</div>
