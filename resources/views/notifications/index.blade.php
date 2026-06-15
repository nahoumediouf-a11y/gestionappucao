@extends('layouts.dashboard')

@section('title', 'Notifications — SIGE UCAO')

@section('page-title', 'Notifications')
@section('page-subtitle', 'Alertes et informations transmises par l\'établissement.')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="list-group list-group-flush">
        @forelse ($notifications as $notification)
            <div class="list-group-item d-flex justify-content-between align-items-start gap-3 {{ $notification->read_at ? '' : 'bg-danger-subtle' }}">
                <div>
                    <div class="fw-bold">
                        @if (! $notification->read_at)
                            <span class="badge bg-danger me-1">Nouveau</span>
                        @endif
                        {{ $notification->data['titre'] ?? 'Notification' }}
                    </div>
                    <div class="text-muted small">{{ $notification->data['message'] ?? '' }}</div>
                    <div class="text-muted small">{{ $notification->created_at->format('d/m/Y H:i') }}</div>
                </div>
                @if (! $notification->read_at)
                    <form method="POST" action="{{ route(auth()->user()->role->value.'.notifications.read', $notification) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-check2"></i> Marquer comme lue
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="list-group-item text-center text-muted py-4">Aucune notification.</div>
        @endforelse
    </div>
</div>

<div class="mt-3">
    {{ $notifications->links() }}
</div>
@endsection
