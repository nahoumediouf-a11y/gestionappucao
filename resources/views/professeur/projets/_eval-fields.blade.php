@php $p = $projet ?? null; @endphp

<div class="col-md-3">
    <label for="bareme" class="form-label">Barème (note max)</label>
    <input type="number" name="bareme" id="bareme" min="1" max="100"
        value="{{ old('bareme', $p->bareme ?? 20) }}"
        class="form-control @error('bareme') is-invalid @enderror">
    @error('bareme')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="col-md-4">
    <label for="ouverture_at" class="form-label">Ouverture du dépôt (optionnel)</label>
    <input type="datetime-local" name="ouverture_at" id="ouverture_at"
        value="{{ old('ouverture_at', isset($p) && $p->ouverture_at ? $p->ouverture_at->format('Y-m-d\TH:i') : '') }}"
        class="form-control @error('ouverture_at') is-invalid @enderror">
    @error('ouverture_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="col-md-5">
    <label for="fermeture_at" class="form-label">Fermeture du dépôt (optionnel)</label>
    <input type="datetime-local" name="fermeture_at" id="fermeture_at"
        value="{{ old('fermeture_at', isset($p) && $p->fermeture_at ? $p->fermeture_at->format('Y-m-d\TH:i') : '') }}"
        class="form-control @error('fermeture_at') is-invalid @enderror">
    <div class="form-text">À défaut, la limite est la fin du jour de l'échéance.</div>
    @error('fermeture_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="col-md-6">
    <div class="form-check">
        <input type="hidden" name="rendu_en_ligne" value="0">
        <input type="checkbox" name="rendu_en_ligne" id="rendu_en_ligne" value="1"
            class="form-check-input" @checked(old('rendu_en_ligne', $p->rendu_en_ligne ?? true))>
        <label for="rendu_en_ligne" class="form-check-label">Autoriser le rendu en ligne par les étudiants</label>
    </div>
</div>

<div class="col-md-6">
    <div class="form-check">
        <input type="hidden" name="copie_unique" value="0">
        <input type="checkbox" name="copie_unique" id="copie_unique" value="1"
            class="form-check-input" @checked(old('copie_unique', $p->copie_unique ?? false))>
        <label for="copie_unique" class="form-check-label">Copie unique (examen : pas de re-soumission)</label>
    </div>
</div>
