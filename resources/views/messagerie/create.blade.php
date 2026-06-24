@extends('layouts.dashboard')

@section('title', 'Nouveau message — SIGE UCAO')

@section('page-title', 'Nouveau message')

@section('page-content')
<form method="POST" action="{{ route('messagerie.store') }}" id="form-message"
      enctype="multipart/form-data" data-suggestions-url="{{ route('recherche.suggestions') }}">
    @csrf

    {{-- Destinataire unique pré-rempli (lien « Répondre ») --}}
    @if ($destinataireId)
        <input type="hidden" name="destinataire_id" value="{{ $destinataireId }}">
    @endif

    @error('destinataires')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-semibold mb-0">Destinataires</label>
                <span class="badge bg-primary" id="compteur-destinataires">0 sélectionné</span>
            </div>
            <p class="text-muted small mb-3">
                Combinez librement des classes, des rôles et des personnes. Les doublons
                sont automatiquement supprimés à l'envoi.
            </p>

            <div class="accordion" id="acc-destinataires">

                {{-- 1. Par classe --}}
                @if ($classesDisponibles->isNotEmpty())
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sec-classes">
                                <i class="bi bi-mortarboard me-2"></i>Par classe
                            </button>
                        </h2>
                        <div id="sec-classes" class="accordion-collapse collapse show" data-bs-parent="#acc-destinataires">
                            <div class="accordion-body">
                                <div class="row g-2">
                                    @foreach ($classesDisponibles as $classe)
                                        <div class="col-sm-6 col-lg-4">
                                            <label class="border rounded p-2 d-flex align-items-center gap-2 h-100">
                                                <input type="checkbox" class="form-check-input m-0 cible-compte" name="classes[]"
                                                       value="{{ $classe->filiere }}|{{ $classe->niveau }}"
                                                       data-effectif="{{ $classe->effectif }}">
                                                <span>
                                                    <span class="fw-semibold">{{ $classe->filiere }} {{ $classe->niveau }}</span><br>
                                                    <small class="text-muted">{{ $classe->effectif }} étudiant(s)</small>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 2. Par rôle --}}
                @if ($rolesDisponibles->isNotEmpty())
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-roles">
                                <i class="bi bi-people me-2"></i>Par rôle
                            </button>
                        </h2>
                        <div id="sec-roles" class="accordion-collapse collapse" data-bs-parent="#acc-destinataires">
                            <div class="accordion-body">
                                <div class="row g-2">
                                    @foreach ($rolesDisponibles as $r)
                                        <div class="col-sm-6 col-lg-4">
                                            <label class="border rounded p-2 d-flex align-items-center gap-2 h-100">
                                                <input type="checkbox" class="form-check-input m-0 cible-compte" name="roles[]"
                                                       value="{{ $r['role']->value }}" data-effectif="{{ $r['nb'] }}">
                                                <span>
                                                    <span class="fw-semibold">Tous : {{ $r['role']->label() }}</span><br>
                                                    <small class="text-muted">{{ $r['nb'] }} compte(s)</small>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 3. Étudiant individuel (recherche) --}}
                @if ($peutCiblerEtudiants)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-etudiants">
                                <i class="bi bi-person-plus me-2"></i>Ajouter un étudiant
                            </button>
                        </h2>
                        <div id="sec-etudiants" class="accordion-collapse collapse" data-bs-parent="#acc-destinataires">
                            <div class="accordion-body">
                                <div class="position-relative">
                                    <input type="search" id="recherche-etudiant" class="form-control" autocomplete="off"
                                           placeholder="Nom, prénom ou matricule..." aria-label="Rechercher un étudiant">
                                    <ul class="recherche-suggestions" id="suggestions-etudiant" role="listbox" hidden></ul>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2" id="chips-etudiants"></div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 4. Personnel individuel --}}
                @if ($personnel->isNotEmpty())
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-personnel">
                                <i class="bi bi-person-badge me-2"></i>Personnel ({{ $personnel->count() }})
                            </button>
                        </h2>
                        <div id="sec-personnel" class="accordion-collapse collapse" data-bs-parent="#acc-destinataires">
                            <div class="accordion-body">
                                <input type="search" class="form-control form-control-sm mb-2" id="filtre-personnel"
                                       placeholder="Filtrer le personnel..." aria-label="Filtrer le personnel">
                                <div class="row g-2" id="liste-personnel">
                                    @foreach ($personnel as $p)
                                        <div class="col-sm-6 col-lg-4 item-personnel" data-nom="{{ Str::lower($p->nom.' '.$p->prenom) }}">
                                            <label class="border rounded p-2 d-flex align-items-center gap-2 h-100">
                                                <input type="checkbox" class="form-check-input m-0 cible-compte" name="users[]" value="{{ $p->id }}" data-effectif="1">
                                                <span>
                                                    <span class="fw-semibold">{{ $p->nom_complet }}</span><br>
                                                    <small class="text-muted">{{ $p->role->label() }}</small>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Contenu du message --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="mb-3">
                <label for="sujet" class="form-label">Sujet</label>
                <input type="text" name="sujet" id="sujet" value="{{ old('sujet') }}"
                    class="form-control @error('sujet') is-invalid @enderror" maxlength="255">
                @error('sujet')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="corps" class="form-label">Message</label>
                <textarea name="corps" id="corps" rows="6" maxlength="5000"
                    class="form-control @error('corps') is-invalid @enderror">{{ old('corps') }}</textarea>
                @error('corps')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="pieces" class="form-label">Pièces jointes <span class="text-muted">(facultatif)</span></label>
                <input type="file" name="pieces[]" id="pieces" multiple
                    class="form-control @error('pieces.*') is-invalid @enderror"
                    accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip">
                <div class="form-text">Images, PDF, Word, Excel, PowerPoint, txt, csv, zip — 5 fichiers max, 8 Mo chacun.</div>
                @error('pieces.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('pieces')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div id="apercu-pieces" class="d-flex flex-wrap gap-2 mt-2"></div>
            </div>
            <button type="submit" class="btn btn-ucao"><i class="bi bi-send me-1"></i>Envoyer</button>
            <a href="{{ route('messagerie.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </div>
</form>
@endsection

@push('styles')
<style>
    .recherche-suggestions {
        position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 1050;
        margin: 0; padding: 4px; list-style: none; max-height: 18rem; overflow-y: auto;
        background: var(--surface, #fff); border: 1px solid rgba(0,0,0,.1);
        border-radius: .5rem; box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }
    .recherche-suggestions__item { display: flex; flex-direction: column; padding: .45rem .6rem; border-radius: .375rem; cursor: pointer; }
    .recherche-suggestions__item:hover, .recherche-suggestions__item.is-active { background: rgba(37,99,235,.1); }
    #chips-etudiants .badge { display: inline-flex; align-items: center; gap: .4rem; font-size: .85rem; }
    #chips-etudiants .badge button { border: 0; background: transparent; color: inherit; line-height: 1; cursor: pointer; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var form = document.getElementById('form-message');
    if (!form) return;

    var compteur = document.getElementById('compteur-destinataires');

    // --- Compteur live (estimation, dédup faite côté serveur) ---
    var chipsEtudiants = new Map(); // id -> label

    function recalculer() {
        var total = 0;
        form.querySelectorAll('.cible-compte:checked').forEach(function (c) {
            total += parseInt(c.dataset.effectif || '1', 10);
        });
        total += chipsEtudiants.size;
        compteur.textContent = total > 0 ? ('≈ ' + total + ' destinataire' + (total > 1 ? 's' : '')) : '0 sélectionné';
        compteur.className = 'badge ' + (total > 0 ? 'bg-primary' : 'bg-secondary');
    }

    form.addEventListener('change', function (e) {
        if (e.target.classList.contains('cible-compte')) recalculer();
    });

    // --- Filtre du personnel ---
    var filtre = document.getElementById('filtre-personnel');
    if (filtre) {
        filtre.addEventListener('input', function () {
            var t = filtre.value.trim().toLowerCase();
            document.querySelectorAll('#liste-personnel .item-personnel').forEach(function (el) {
                el.style.display = el.dataset.nom.indexOf(t) >= 0 ? '' : 'none';
            });
        });
    }

    // --- Recherche d'étudiants (réutilise recherche.suggestions) -> chips ---
    var input = document.getElementById('recherche-etudiant');
    if (input) {
        var liste = document.getElementById('suggestions-etudiant');
        var conteneurChips = document.getElementById('chips-etudiants');
        var url = form.dataset.suggestionsUrl;
        var minuteur = null, controleur = null, actif = -1;

        function fermer() { liste.hidden = true; liste.innerHTML = ''; actif = -1; }

        function ajouterChip(id, label) {
            if (chipsEtudiants.has(id)) return;
            chipsEtudiants.set(id, label);
            var badge = document.createElement('span');
            badge.className = 'badge text-bg-primary';
            badge.dataset.id = id;
            badge.innerHTML = '<input type="hidden" name="etudiants[]" value="' + id + '">' +
                '<i class="bi bi-person-fill"></i>' + label.replace(/[<>&]/g, '') +
                ' <button type="button" aria-label="Retirer">&times;</button>';
            badge.querySelector('button').addEventListener('click', function () {
                chipsEtudiants.delete(id); badge.remove(); recalculer();
            });
            conteneurChips.appendChild(badge);
            recalculer();
        }

        function afficher(resultats) {
            liste.innerHTML = '';
            if (!resultats.length) { fermer(); return; }
            resultats.forEach(function (r) {
                if (!r.id) return;
                var li = document.createElement('li'); li.setAttribute('role', 'option');
                li.className = 'recherche-suggestions__item';
                li.innerHTML = '<span class="fw-semibold">' + (r.label || '').replace(/[<>&]/g, '') + '</span>' +
                               '<small class="text-muted">' + (r.sous_titre || '').replace(/[<>&]/g, '') + '</small>';
                li.addEventListener('click', function () { ajouterChip(String(r.id), r.label || ''); input.value = ''; fermer(); });
                liste.appendChild(li);
            });
            liste.hidden = false; actif = -1;
        }

        input.addEventListener('input', function () {
            var terme = input.value.trim();
            clearTimeout(minuteur);
            if (terme.length < 2) { fermer(); return; }
            minuteur = setTimeout(function () {
                if (controleur) controleur.abort();
                controleur = new AbortController();
                fetch(url + '?q=' + encodeURIComponent(terme) + '&type=etudiant',
                      { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, signal: controleur.signal })
                    .then(function (r) { return r.ok ? r.json() : []; })
                    .then(function (d) { afficher(Array.isArray(d) ? d : []); })
                    .catch(function () {});
            }, 200);
        });

        document.addEventListener('click', function (e) {
            if (e.target !== input && !liste.contains(e.target)) fermer();
        });
    }

    // --- Aperçu des pièces jointes sélectionnées ---
    var champPieces = document.getElementById('pieces');
    if (champPieces) {
        var apercu = document.getElementById('apercu-pieces');
        champPieces.addEventListener('change', function () {
            apercu.innerHTML = '';
            Array.prototype.forEach.call(champPieces.files, function (f) {
                var ko = Math.max(1, Math.round(f.size / 1024));
                var taille = ko >= 1024 ? (Math.round(ko / 102.4) / 10 + ' Mo') : (ko + ' Ko');
                var est = f.type.indexOf('image/') === 0 ? 'bi-image' : 'bi-paperclip';
                var badge = document.createElement('span');
                badge.className = 'badge text-bg-light border';
                badge.innerHTML = '<i class="bi ' + est + ' me-1"></i>' +
                    f.name.replace(/[<>&]/g, '') + ' <small class="text-muted">(' + taille + ')</small>';
                apercu.appendChild(badge);
            });
        });
    }

    // --- Confirmation pour les envois massifs ---
    form.addEventListener('submit', function (e) {
        var total = 0;
        form.querySelectorAll('.cible-compte:checked').forEach(function (c) { total += parseInt(c.dataset.effectif || '1', 10); });
        total += chipsEtudiants.size;
        if (total > 20 && !window.confirm('Envoyer ce message à environ ' + total + ' destinataires ?')) {
            e.preventDefault();
        }
    });

    recalculer();
})();
</script>
@endpush
