@extends('layouts.dashboard')

@section('title', 'Emploi du temps — SIGE UCAO')

@section('page-title', 'Mon emploi du temps')

@section('page-actions')
    <a href="{{ route('professeur.edt.pdf') }}" class="btn btn-outline-secondary">
        <i class="bi bi-download me-1"></i>Télécharger PDF
    </a>
@endsection

@section('page-content')
    @include('partials.edt-grille', ['creneaux' => $creneaux, 'contexte' => 'professeur'])
@endsection
