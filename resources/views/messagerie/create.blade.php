@extends('layouts.dashboard')

@section('title', 'Nouveau message — SIGE UCAO')

@section('page-title', 'Nouveau message')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('messagerie.store') }}">
            @csrf
            <div class="mb-3">
                <label for="destinataire_id" class="form-label">Destinataire</label>
                <select name="destinataire_id" id="destinataire_id" class="form-select @error('destinataire_id') is-invalid @enderror">
                    <option value="">— Choisir un destinataire —</option>
                    @foreach ($destinataires as $d)
                        <option value="{{ $d->id }}" @selected(old('destinataire_id', $destinataireId) == $d->id)>
                            {{ $d->nom_complet }} ({{ $d->role->label() }})
                        </option>
                    @endforeach
                </select>
                @error('destinataire_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="sujet" class="form-label">Sujet</label>
                <input type="text" name="sujet" id="sujet" value="{{ old('sujet') }}"
                    class="form-control @error('sujet') is-invalid @enderror">
                @error('sujet')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="corps" class="form-label">Message</label>
                <textarea name="corps" id="corps" rows="6"
                    class="form-control @error('corps') is-invalid @enderror">{{ old('corps') }}</textarea>
                @error('corps')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-ucao"><i class="bi bi-send me-1"></i>Envoyer</button>
            <a href="{{ route('messagerie.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </form>
    </div>
</div>
@endsection
