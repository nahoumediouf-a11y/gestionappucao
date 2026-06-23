@extends('layouts.dashboard')

@section('title', 'Messagerie — SIGE UCAO')

@section('page-title', 'Messagerie')

@section('page-actions')
    <a href="{{ route('messagerie.create') }}" class="btn btn-ucao">
        <i class="bi bi-pencil-square me-1"></i>Nouveau message
    </a>
@endsection

@section('page-content')
<ul class="nav nav-pills mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $onglet === 'recus' ? 'active' : '' }}" href="{{ route('messagerie.index') }}">
            <i class="bi bi-inbox me-1"></i>Reçus
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $onglet === 'envoyes' ? 'active' : '' }}" href="{{ route('messagerie.envoyes') }}">
            <i class="bi bi-send me-1"></i>Envoyés
        </a>
    </li>
</ul>

<div class="card border-0 shadow-sm">
    <div class="list-group list-group-flush">
        @forelse ($messages as $message)
            @php $nonLu = $onglet === 'recus' && ! $message->estLu(); @endphp
            <a href="{{ route('messagerie.show', $message) }}"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 {{ $nonLu ? 'fw-semibold' : '' }}">
                <div class="text-truncate">
                    @if ($nonLu)<span class="badge bg-primary me-1">Nouveau</span>@endif
                    <i class="bi {{ $onglet === 'recus' ? 'bi-person' : 'bi-person-fill-up' }} text-muted me-1"></i>
                    @if ($onglet === 'recus')
                        {{ $message->expediteur->nom_complet }}
                    @else
                        À {{ $message->destinataire->nom_complet }}
                    @endif
                    <span class="mx-2 text-muted">—</span>{{ $message->sujet }}
                </div>
                <small class="text-muted flex-shrink-0">{{ $message->created_at->format('d/m/Y H:i') }}</small>
            </a>
        @empty
            <div class="list-group-item text-center text-muted py-4">Aucun message.</div>
        @endforelse
    </div>
    @if ($messages->hasPages())
        <div class="card-body">{{ $messages->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
