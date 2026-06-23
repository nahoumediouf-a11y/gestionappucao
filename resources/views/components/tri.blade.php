@props(['colonne', 'tri', 'dir'])
@php
    $actif = $tri === $colonne;
    $nouvelleDir = ($actif && $dir === 'asc') ? 'desc' : 'asc';
    $icone = ! $actif ? 'bi-arrow-down-up text-muted' : ($dir === 'asc' ? 'bi-sort-down-alt' : 'bi-sort-up-alt');
    $url = request()->fullUrlWithQuery(['tri' => $colonne, 'dir' => $nouvelleDir]);
@endphp
<a href="{{ $url }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1"
   @if ($actif) aria-sort="{{ $dir === 'asc' ? 'ascending' : 'descending' }}" @endif>
    {{ $slot }} <i class="bi {{ $icone }} small"></i>
</a>
