@extends('layouts.dashboard')

@section('title', $message->sujet.' — Messagerie — SIGE UCAO')

@section('page-title', $message->sujet)

@section('page-actions')
    @php $repondreA = auth()->id() === $message->destinataire_id ? $message->expediteur_id : $message->destinataire_id; @endphp
    <a href="{{ route('messagerie.create', ['a' => $repondreA]) }}" class="btn btn-ucao">
        <i class="bi bi-reply me-1"></i>Répondre
    </a>
    <form method="POST" action="{{ route('messagerie.destroy', $message) }}" class="d-inline" onsubmit="return confirm('Supprimer ce message ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Supprimer</button>
    </form>
@endsection

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3 pb-3 border-bottom">
            <div>
                <div><span class="text-muted">De :</span> <strong>{{ $message->expediteur->nom_complet }}</strong>
                    <span class="badge bg-primary-subtle text-primary">{{ $message->expediteur->role->label() }}</span></div>
                <div><span class="text-muted">À :</span> <strong>{{ $message->destinataire->nom_complet }}</strong>
                    <span class="badge bg-primary-subtle text-primary">{{ $message->destinataire->role->label() }}</span></div>
            </div>
            <small class="text-muted">{{ $message->created_at->format('d/m/Y à H:i') }}</small>
        </div>
        <div style="white-space: pre-line;">{{ $message->corps }}</div>
    </div>
</div>

<a href="{{ url()->previous() }}" class="btn btn-outline-secondary mt-3">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection
