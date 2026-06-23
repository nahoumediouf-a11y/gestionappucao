@csrf

@if ($creneaux->isNotEmpty())
    <div class="mb-3">
        <label for="creneau" class="form-label">Rattacher à un créneau de mon emploi du temps</label>
        <select id="creneau" class="form-select" onchange="ucaoRemplirCreneau(this)">
            <option value="">-- Choisir un raccourci (optionnel) --</option>
            @foreach ($creneaux as $creneau)
                <option value="{{ $creneau->id }}|{{ $creneau->filiere }}|{{ $creneau->niveau }}|{{ $creneau->matiere }}">
                    {{ $creneau->filiere }} {{ $creneau->niveau }} — {{ $creneau->matiere }} ({{ $creneau->jour }} {{ \Illuminate\Support\Str::substr($creneau->heure_debut, 0, 5) }})
                </option>
            @endforeach
        </select>
        <div class="form-text">Pré-remplit la filière, le niveau et le titre à partir d'une de vos classes.</div>
    </div>
@endif

<input type="hidden" name="emploi_du_temps_id" id="emploi_du_temps_id" value="{{ old('emploi_du_temps_id', $cours->emploi_du_temps_id ?? '') }}">

<div class="row g-3">
    <div class="col-md-12">
        <label for="titre" class="form-label">Titre de la séance</label>
        <input type="text" name="titre" id="titre" value="{{ old('titre', $cours->titre ?? '') }}"
            class="form-control @error('titre') is-invalid @enderror" placeholder="Ex : Algorithmique — révision du chapitre 3">
        @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="filiere" class="form-label">Filière</label>
        <input type="text" name="filiere" id="filiere" value="{{ old('filiere', $cours->filiere ?? '') }}"
            class="form-control @error('filiere') is-invalid @enderror">
        @error('filiere')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="niveau" class="form-label">Niveau</label>
        <input type="text" name="niveau" id="niveau" value="{{ old('niveau', $cours->niveau ?? '') }}"
            class="form-control @error('niveau') is-invalid @enderror">
        @error('niveau')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="debut_prevu" class="form-label">Début prévu</label>
        <input type="datetime-local" name="debut_prevu" id="debut_prevu"
            value="{{ old('debut_prevu', isset($cours) ? $cours->debut_prevu->format('Y-m-d\TH:i') : '') }}"
            class="form-control @error('debut_prevu') is-invalid @enderror">
        @error('debut_prevu')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="fin_prevue" class="form-label">Fin prévue (optionnel)</label>
        <input type="datetime-local" name="fin_prevue" id="fin_prevue"
            value="{{ old('fin_prevue', isset($cours) && $cours->fin_prevue ? $cours->fin_prevue->format('Y-m-d\TH:i') : '') }}"
            class="form-control @error('fin_prevue') is-invalid @enderror">
        @error('fin_prevue')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description / consignes (optionnel)</label>
        <textarea name="description" id="description" rows="3"
            class="form-control @error('description') is-invalid @enderror">{{ old('description', $cours->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

@if ($creneaux->isNotEmpty())
    <script>
        function ucaoRemplirCreneau(select) {
            if (! select.value) { return; }
            var parts = select.value.split('|');
            document.getElementById('emploi_du_temps_id').value = parts[0];
            document.getElementById('filiere').value = parts[1];
            document.getElementById('niveau').value = parts[2];
            if (! document.getElementById('titre').value) {
                document.getElementById('titre').value = parts[3];
            }
        }
    </script>
@endif
