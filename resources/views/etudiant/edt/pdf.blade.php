<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $titre }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #212529; font-size: 11px; }
        .header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { font-size: 16px; margin: 0; color: #0d6efd; }
        .header p { margin: 3px 0 0; color: #6c757d; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dee2e6; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #f1f3f5; font-size: 10px; text-transform: uppercase; }
        .jour { background: #f8f9fa; font-weight: bold; width: 90px; }
        .type { display: inline-block; padding: 1px 5px; border-radius: 3px; color: #fff; font-size: 9px; }
        .CM { background: #0d6efd; }
        .TD { background: #198754; }
        .TP { background: #fd7e14; }
        .Examen { background: #dc3545; }
        .muted { color: #6c757d; font-size: 10px; }
        .footer { margin-top: 14px; text-align: right; color: #adb5bd; font-size: 9px; }
    </style>
</head>
<body>
    @php
        $couleurs = ['CM' => 'CM', 'TD' => 'TD', 'TP' => 'TP', 'Examen' => 'Examen'];
        $parJour = collect($creneaux)->groupBy('jour');
    @endphp

    <div class="header">
        <h1>SIGE UCAO — {{ $titre }}</h1>
        <p>{{ $sousTitre }} · Édité le {{ now()->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Jour</th>
                <th>Horaire</th>
                <th>Matière</th>
                <th>Type</th>
                <th>Salle</th>
                <th>{{ $contexte === 'etudiant' ? 'Professeur' : 'Classe' }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse (\App\Models\EmploiDuTemps::JOURS as $jour)
                @php $duJour = ($parJour[$jour] ?? collect())->sortBy('heure_debut'); @endphp
                @foreach ($duJour as $i => $c)
                    <tr>
                        @if ($i === 0)
                            <td class="jour" rowspan="{{ $duJour->count() }}">{{ $jour }}</td>
                        @endif
                        <td>{{ \Illuminate\Support\Carbon::parse($c->heure_debut)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($c->heure_fin)->format('H:i') }}</td>
                        <td>{{ $c->matiere }}</td>
                        <td><span class="type {{ $c->type }}">{{ $c->type }}</span></td>
                        <td>{{ $c->salle ?: '—' }}</td>
                        <td>
                            @if ($contexte === 'etudiant')
                                {{ $c->professeur?->nom_complet ?? '—' }}
                            @else
                                {{ $c->filiere }} {{ $c->niveau }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            @empty
            @endforelse
            @if (collect($creneaux)->isEmpty())
                <tr><td colspan="6" class="muted" style="text-align:center; padding:14px;">Aucun créneau programmé.</td></tr>
            @endif
        </tbody>
    </table>

    <div class="footer">SIGE UCAO Saint Michel — document généré automatiquement</div>
</body>
</html>
