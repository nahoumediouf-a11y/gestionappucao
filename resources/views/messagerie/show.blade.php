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

        @if ($message->piecesJointes->isNotEmpty())
            <div class="mt-4 pt-3 border-top">
                <h6 class="text-muted mb-3"><i class="bi bi-paperclip me-1"></i>{{ $message->piecesJointes->count() }} pièce(s) jointe(s)</h6>
                <div class="row g-3">
                    @foreach ($message->piecesJointes as $piece)
                        @php $lien = route('messagerie.piece-jointe', $piece); @endphp
                        <div class="col-sm-6 col-lg-4">
                            <div class="border rounded p-2 h-100 d-flex flex-column">
                                @if ($piece->estImage())
                                    <a href="{{ $lien }}" target="_blank" class="d-block mb-2">
                                        <img src="{{ $lien }}" alt="{{ $piece->nom }}" class="img-fluid rounded" style="max-height:160px;width:100%;object-fit:cover;">
                                    </a>
                                @else
                                    <div class="text-center py-3 mb-2 bg-light rounded"><i class="bi bi-file-earmark-text fs-1 text-muted"></i></div>
                                @endif
                                <div class="small text-truncate fw-semibold" title="{{ $piece->nom }}">{{ $piece->nom }}</div>
                                <div class="small text-muted mb-2">{{ $piece->tailleLisible() }}</div>
                                <a href="{{ $lien }}" class="btn btn-sm btn-outline-secondary mt-auto">
                                    <i class="bi bi-download me-1"></i>Télécharger
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<a href="{{ url()->previous() }}" class="btn btn-outline-secondary mt-3">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection
