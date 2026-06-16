@extends('layouts.dashboard')

@section('title', 'Tableau de bord — SIGE UCAO')

@section('page-title', 'Tableau de bord')
@section('page-subtitle', "Modules accessibles selon votre rôle.")

@section('page-content')
<div class="row g-3">
    @forelse ($modules as $module)
        @php
            $href      = isset($module['url']) ? $module['url'] : route($module['route']);
            $highlight = ! empty($module['highlight']);
        @endphp
        <div class="col-md-6 col-lg-4">
            <a href="{{ $href }}" class="text-decoration-none text-reset">
                <div class="card h-100 border-0 shadow-sm {{ $highlight ? 'border border-danger border-2' : '' }}"
                     style="{{ $highlight ? 'box-shadow: 0 0 0 3px rgba(220,53,69,.15) !important;' : '' }}">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-{{ $module['color'] }} {{ $highlight ? 'bg-opacity-25' : 'bg-opacity-10' }} text-{{ $module['color'] }} p-3">
                                <i class="bi {{ $module['icon'] }} fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h3 class="h6 mb-1 {{ $highlight ? 'text-danger fw-bold' : '' }}">
                                    {{ $module['label'] }}
                                    @if (! empty($module['badge']))
                                        <span class="badge bg-{{ $highlight ? 'danger' : 'danger' }} rounded-pill ms-1">{{ $module['badge'] }}</span>
                                    @endif
                                </h3>
                                @if ($highlight)
                                    <p class="small text-danger mb-0 fw-semibold">
                                        <i class="bi bi-arrow-right-circle me-1"></i>Cliquez pour payer maintenant
                                    </p>
                                @else
                                    <p class="small text-muted mb-0">Module autorisé pour {{ $user->role->label() }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning">Aucun module accessible.</div>
        </div>
    @endforelse
</div>
@endsection
