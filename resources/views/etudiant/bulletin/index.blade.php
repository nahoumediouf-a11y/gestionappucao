@extends('layouts.app')

@section('title', 'Bulletin — SIGE UCAO')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            @if (session('error'))
                <div class="alert alert-danger d-print-none">{{ session('error') }}</div>
            @endif

            @unless ($etudiant->estEnRegleAvecRecouvrement())
                <div class="alert alert-warning d-print-none d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div>
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Vous avez un solde impayé de <strong>{{ number_format($etudiant->solde, 0, ',', ' ') }} FCFA</strong>.
                        Le téléchargement du bulletin est bloqué tant que votre situation n'est pas régularisée.
                    </div>
                    <a href="{{ route('etudiant.paiements.index') }}#payer-scolarite" class="btn btn-danger btn-sm text-nowrap">
                        <i class="bi bi-credit-card-fill me-1"></i>Payer maintenant
                    </a>
                </div>
            @endunless

            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h2 class="h4 mb-0"><i class="bi bi-bank2 me-2"></i>UCAO Saint Michel</h2>
                            <p class="text-muted mb-0">Bulletin de notes</p>
                        </div>
                        <span class="badge bg-primary fs-6">{{ $etudiant->filiere }} {{ $etudiant->niveau }}</span>
                    </div>

                    <hr>

                    <div class="row mb-4">
                        <div class="col-6">
                            <p class="text-muted small mb-1">Étudiant</p>
                            <p class="fw-bold mb-0">{{ $etudiant->user->nom_complet }}</p>
                            <p class="text-muted small">{{ $etudiant->matricule }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted small mb-1">Moyenne générale</p>
                            <p class="display-6 fw-bold text-success mb-0">{{ $moyenneGenerale }} / 20</p>
                        </div>
                    </div>

                    @forelse ($parSession as $session => $data)
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">{{ $session }}</h6>
                                <span class="badge bg-light text-dark border">Moyenne : {{ $data['moyenne'] }} / 20</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Matière</th>
                                            <th class="text-end">Note / 20</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data['notes'] as $note)
                                            <tr>
                                                <td>{{ $note->matiere }}</td>
                                                <td class="text-end fw-bold">{{ $note->valeur }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">Aucune note disponible pour le moment.</div>
                    @endforelse
                </div>
            </div>

            <div class="text-center mt-3 d-print-none">
                @if ($etudiant->estEnRegleAvecRecouvrement())
                    <a href="{{ route('etudiant.bulletin.pdf') }}" class="btn btn-ucao">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Télécharger en PDF
                    </a>
                @else
                    <button type="button" class="btn btn-ucao" disabled title="Solde impayé : régularisez votre situation pour débloquer le téléchargement">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Télécharger en PDF
                    </button>
                @endif
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="bi bi-printer me-1"></i>Imprimer
                </button>
                <a href="{{ route('etudiant.notes.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
