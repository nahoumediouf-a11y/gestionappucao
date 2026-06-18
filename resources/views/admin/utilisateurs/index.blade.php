@extends('layouts.dashboard')

@section('title', 'Gestion des utilisateurs — SIGE UCAO')

@section('page-title', 'Gestion des utilisateurs')
@section('page-subtitle', 'Ajouter, modifier ou supprimer un utilisateur et gérer les rôles.')

@section('page-actions')
    <a href="{{ route('admin.utilisateurs.create') }}" class="btn btn-ucao">
        <i class="bi bi-person-plus me-1"></i>Ajouter un utilisateur
    </a>
@endsection

@section('page-content')
@if ($enAttenteCount > 0)
    <div class="alert alert-warning d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-hourglass-split me-1"></i>
            <strong>{{ $enAttenteCount }}</strong> compte(s) en attente de validation.
        </div>
        <a href="{{ route('admin.utilisateurs.index', ['statut' => 'en_attente']) }}" class="btn btn-sm btn-warning">
            Voir les demandes
        </a>
    </div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="q" id="ucao-search-q" class="form-control" placeholder="Rechercher par nom, login, email ou matricule..." value="{{ $q }}" autocomplete="off">
            </div>
            <div class="col-md-3">
                <select name="statut" id="ucao-search-statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" {{ $statut === 'en_attente' ? 'selected' : '' }}>En attente de validation</option>
                    <option value="actif" {{ $statut === 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="inactif" {{ $statut === 'inactif' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Rechercher</button>
            </div>
        </form>
        @push('scripts')
        <script>
        (function () {
            var timer;
            var form = document.querySelector('form[method="GET"]');
            var qInput = document.getElementById('ucao-search-q');
            var statutSelect = document.getElementById('ucao-search-statut');
            function submit() { form.submit(); }
            if (qInput) {
                qInput.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(submit, 400);
                });
            }
            if (statutSelect) {
                statutSelect.addEventListener('change', submit);
            }
        })();
        </script>
        @endpush
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Login</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Détails</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->nom_complet }}</td>
                        <td><code>{{ $user->login }}</code></td>
                        <td>{{ $user->email ?? '—' }}</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $user->role->label() }}</span></td>
                        <td>
                            @php
                                $statutLabels = ['actif' => 'Actif', 'inactif' => 'Inactif', 'en_attente' => 'En attente'];
                                $statutColors = ['actif' => 'success', 'inactif' => 'secondary', 'en_attente' => 'warning'];
                            @endphp
                            <span class="badge bg-{{ $statutColors[$user->statut] ?? 'secondary' }}">
                                {{ $statutLabels[$user->statut] ?? ucfirst($user->statut) }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            @if ($user->etudiant)
                                {{ $user->etudiant->matricule }} — {{ $user->etudiant->filiere }} {{ $user->etudiant->niveau }}
                                — Solde: {{ number_format($user->etudiant->solde, 0, ',', ' ') }} FCFA
                                @if ($user->etudiant->enSituationRouge())
                                    <span class="badge bg-danger ms-1">Situation rouge</span>
                                @endif
                                <br>
                                <i class="bi bi-telephone"></i> {{ $user->telephone ?? '—' }}
                                · <i class="bi bi-geo-alt"></i> {{ $user->etudiant->adresse ?? '—' }}
                                <br>
                                <i class="bi bi-cake2"></i> Né(e) le {{ optional($user->etudiant->date_naissance)->format('d/m/Y') ?? '—' }}
                                à {{ $user->etudiant->lieu_naissance ?? '—' }}
                                <br>
                                <i class="bi bi-person-heart"></i> Urgence : {{ $user->etudiant->contact_urgence_nom ?? '—' }}
                                ({{ $user->etudiant->contact_urgence_telephone ?? '—' }})
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($user->statut === 'en_attente')
                                <form method="POST" action="{{ route('admin.utilisateurs.activer', $user) }}" class="d-inline" onsubmit="return confirm('Activer ce compte ? Une notification sera envoyée à l\'utilisateur.');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-circle"></i> Activer
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.utilisateurs.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.utilisateurs.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <div class="card-footer bg-white">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
