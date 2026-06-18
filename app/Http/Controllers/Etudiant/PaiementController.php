<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/** Frais de scolarité annuels (FCFA) par niveau. Modifiable ici. */
const SCOLARITE_PAR_NIVEAU = [
    'L1' => 650_000,
    'L2' => 700_000,
    'L3' => 780_000,
    'M1' => 850_000,
    'M2' => 900_000,
];

class PaiementController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;
        $paiements = $etudiant->paiements()->orderByDesc('date_paiement')->get();

        // Scolarité annuelle estimée
        $niveauKey = strtoupper($etudiant->niveau ?? '');
        // Handle LIG L3-2 → L3
        if (str_contains($niveauKey, 'L3')) $niveauKey = 'L3';
        $scolariteTotale = SCOLARITE_PAR_NIVEAU[$niveauKey] ?? 780_000;
        $nbTranches = 6;
        $montantTranche = (int) round($scolariteTotale / $nbTranches);
        $totalPaye = $paiements->where('statut', 'valide')->sum('montant');
        $progression = $scolariteTotale > 0 ? min(100, (int) round($totalPaye / $scolariteTotale * 100)) : 0;

        // Générer le plan de paiement en 6 tranches (à partir de septembre)
        $moisDebut = 9; // Septembre
        $annee = (int) date('Y', strtotime(now()->month >= 9 ? 'now' : '-1 year'));
        $tranches = [];
        for ($i = 0; $i < $nbTranches; $i++) {
            $mois = (($moisDebut - 1 + $i) % 12) + 1;
            $yr = $annee + (($moisDebut - 1 + $i) >= 12 ? 1 : 0);
            $echeance = \Carbon\Carbon::create($yr, $mois, 1)->endOfMonth();
            $montant = ($i === $nbTranches - 1)
                ? $scolariteTotale - $montantTranche * ($nbTranches - 1)
                : $montantTranche;
            $cumul = $montantTranche * ($i + 1);
            $paye = $totalPaye >= $cumul;
            $tranches[] = [
                'numero' => $i + 1,
                'mois' => $echeance->translatedFormat('F Y'),
                'echeance' => $echeance,
                'montant' => $montant,
                'paye' => $paye,
                'en_retard' => !$paye && $echeance->isPast(),
            ];
        }

        return view('etudiant.paiements.index', [
            'etudiant' => $etudiant,
            'paiements' => $paiements,
            'engagements' => $etudiant->engagements()->orderByDesc('echeance')->get(),
            'scolariteTotale' => $scolariteTotale,
            'montantTranche' => $montantTranche,
            'nbTranches' => $nbTranches,
            'totalPaye' => $totalPaye,
            'progression' => $progression,
            'tranches' => $tranches,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $etudiant = auth()->user()->etudiant;

        $validated = $request->validate([
            'montant'        => ['required', 'numeric', 'min:1', 'max:'.(float) $etudiant->solde],
            'mode_paiement'  => ['required', 'string', 'in:'.implode(',', array_keys(Paiement::MODES))],
            'reference'      => ['required', 'string', 'unique:paiements,reference'],
            'numero_mobile'  => ['nullable', 'string', 'max:20'],
            'note_etudiant'  => ['nullable', 'string', 'max:500'],
        ], [
            'montant.max' => 'Le montant ne peut pas dépasser votre solde restant (:max FCFA).',
        ]);

        Paiement::create([
            'etudiant_id'    => $etudiant->id,
            'date_paiement'  => now()->toDateString(),
            'montant'        => $validated['montant'],
            'mode_paiement'  => $validated['mode_paiement'],
            'reference'      => $validated['reference'],
            'numero_mobile'  => $validated['numero_mobile'] ?? null,
            'note_etudiant'  => $validated['note_etudiant'] ?? null,
            'statut'         => 'en_attente_validation',
        ]);

        return back()->with('success', 'Déclaration envoyée avec succès. Elle sera validée par le service comptable.');
    }

    public function recu(Paiement $paiement): View
    {
        abort_unless($paiement->etudiant_id === auth()->user()->etudiant->id, Response::HTTP_FORBIDDEN);

        $paiement->load(['etudiant.user', 'agent']);

        return view('comptabilite.paiements.recu', [
            'paiement' => $paiement,
        ]);
    }
}
