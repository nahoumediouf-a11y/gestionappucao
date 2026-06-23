{{--
    Bloc identité réutilisable.
    Variables : $identite (App\Models\User), $sousTitre (string|null), $taille ('sm'|'md').
--}}
@php
    $taille = $taille ?? 'md';
    $dim = $taille === 'sm' ? 36 : 52;
    $initiales = strtoupper(mb_substr($identite->prenom ?? '', 0, 1).mb_substr($identite->nom ?? '', 0, 1));
    $statutColors = ['actif' => 'success', 'inactif' => 'secondary', 'en_attente' => 'warning'];
    $statutLabels = ['actif' => 'Actif', 'inactif' => 'Inactif', 'en_attente' => 'En attente'];
@endphp
<div class="d-flex align-items-center gap-2">
    @if ($identite->photoUrl())
        <img src="{{ $identite->photoUrl() }}" alt="Photo de {{ $identite->nom_complet }}" loading="lazy"
             class="rounded-circle flex-shrink-0 object-fit-cover border"
             style="width: {{ $dim }}px; height: {{ $dim }}px; object-fit: cover;">
    @else
        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-semibold flex-shrink-0"
              style="width: {{ $dim }}px; height: {{ $dim }}px; font-size: {{ $taille === 'sm' ? '.85rem' : '1.1rem' }};"
              aria-hidden="true">{{ $initiales ?: '?' }}</span>
    @endif
    <div class="lh-sm">
        <div class="fw-semibold">{{ $identite->nom_complet }}</div>
        <div class="small">
            <span class="badge bg-primary-subtle text-primary">{{ $identite->role->label() }}</span>
            <span class="badge bg-{{ $statutColors[$identite->statut] ?? 'secondary' }}">{{ $statutLabels[$identite->statut] ?? ucfirst($identite->statut) }}</span>
        </div>
        @if (! empty($sousTitre))
            <div class="text-muted small">{{ $sousTitre }}</div>
        @endif
    </div>
</div>
