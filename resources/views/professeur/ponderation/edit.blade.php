@extends('layouts.dashboard')

@section('title', 'Pondération — SIGE UCAO')

@section('page-title', 'Pondération des notes')
@section('page-subtitle', $matiere.' — '.$filiere.' '.$niveau)

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <p class="text-muted">Définissez le poids de chaque catégorie dans la moyenne de la matière. La somme doit faire <strong>100 %</strong>. Une catégorie à 0 % n'est pas comptée.</p>

        <div class="mb-3 d-flex flex-wrap gap-2">
            <span class="small text-muted align-self-center me-1">Modèles rapides :</span>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="ucaoPreset(70,30,0,0)">TP 30 / Examen 70</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="ucaoPreset(40,60,0,0)">TP 60 / Examen 40</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="ucaoPreset(100,0,0,0)">Examen 100</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="ucaoPreset(50,50,0,0)">TP 50 / Examen 50</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="ucaoPreset(60,0,0,40)">CC 40 / Examen 60</button>
        </div>

        <form method="POST" action="{{ route('professeur.ponderation.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="filiere" value="{{ $filiere }}">
            <input type="hidden" name="niveau" value="{{ $niveau }}">
            <input type="hidden" name="matiere" value="{{ $matiere }}">

            @error('poids')<div class="alert alert-danger">{{ $message }}</div>@enderror

            <div class="row g-3">
                @foreach (['examen' => 'Examen', 'tp' => 'Travaux pratiques (TP)', 'td' => 'Travaux dirigés (TD)', 'cc' => 'Contrôle continu (CC)'] as $cle => $libelle)
                    <div class="col-md-3 col-6">
                        <label for="poids_{{ $cle }}" class="form-label">{{ $libelle }}</label>
                        <div class="input-group">
                            <input type="number" min="0" max="100" name="poids_{{ $cle }}" id="poids_{{ $cle }}"
                                value="{{ old('poids_'.$cle, $ponderation->{'poids_'.$cle}) }}"
                                class="form-control ucao-poids" oninput="ucaoTotal()">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                Total : <span id="ucao-total" class="badge bg-secondary">—</span>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao"><i class="bi bi-save me-1"></i>Enregistrer</button>
                <a href="{{ route('professeur.carnet.index', ['filiere' => $filiere, 'niveau' => $niveau, 'matiere' => $matiere]) }}" class="btn btn-outline-secondary">Retour au carnet</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function ucaoTotal() {
        var t = 0;
        document.querySelectorAll('.ucao-poids').forEach(function (i) { t += parseInt(i.value || 0, 10); });
        var b = document.getElementById('ucao-total');
        b.textContent = t + ' %';
        b.className = 'badge bg-' + (t === 100 ? 'success' : 'danger');
        return t;
    }
    function ucaoPreset(ex, tp, td, cc) {
        document.getElementById('poids_examen').value = ex;
        document.getElementById('poids_tp').value = tp;
        document.getElementById('poids_td').value = td;
        document.getElementById('poids_cc').value = cc;
        ucaoTotal();
    }
    document.addEventListener('DOMContentLoaded', ucaoTotal);
</script>
@endpush
@endsection
