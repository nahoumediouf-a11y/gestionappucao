{{--
    Grille hebdomadaire d'emploi du temps.
    Paramètres :
      $creneaux : collection de App\Models\EmploiDuTemps
      $contexte : 'etudiant' (affiche salle + prof) ou 'professeur' (affiche classe + salle)
--}}
@php
    $contexte = $contexte ?? 'etudiant';
    $parJour = collect($creneaux)->groupBy('jour');
@endphp

<div class="row g-3">
    @foreach (\App\Models\EmploiDuTemps::JOURS as $jour)
        @php $duJour = ($parJour[$jour] ?? collect())->sortBy('heure_debut'); @endphp
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2 fw-semibold">
                    <i class="bi bi-calendar-day me-1 text-primary"></i>{{ $jour }}
                    <span class="badge bg-light text-muted float-end">{{ $duJour->count() }}</span>
                </div>
                <div class="card-body p-2">
                    @forelse ($duJour as $c)
                        <div class="border-start border-4 border-{{ $c->typeCouleur() }} bg-light rounded-end p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="fw-semibold">{{ $c->matiere }}</span>
                                <span class="badge bg-{{ $c->typeCouleur() }}">{{ $c->type }}</span>
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-clock me-1"></i>{{ \Illuminate\Support\Carbon::parse($c->heure_debut)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($c->heure_fin)->format('H:i') }}
                            </div>
                            <div class="small text-muted">
                                <i class="bi bi-geo-alt me-1"></i>Salle {{ $c->salle ?: '—' }}
                            </div>
                            @if ($contexte === 'etudiant')
                                <div class="small text-muted">
                                    <i class="bi bi-person me-1"></i>{{ $c->professeur?->nom_complet ?? '—' }}
                                </div>
                            @else
                                <div class="small text-muted">
                                    <i class="bi bi-mortarboard me-1"></i>{{ $c->filiere }} {{ $c->niveau }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted small py-3">Aucun cours</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-3 small text-muted">
    <span class="me-3">Légende :</span>
    @foreach (\App\Models\EmploiDuTemps::TYPES as $code => $info)
        <span class="badge bg-{{ $info['couleur'] }} me-1">{{ $code }}</span><span class="me-3">{{ $info['label'] }}</span>
    @endforeach
</div>
