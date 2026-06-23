<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Services\PaydunyaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PaiementController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;
        $paiements = $etudiant->paiements()->orderByDesc('date_paiement')->get();

        // Scolarité annuelle estimée
        $scolariteTotale = $etudiant->scolariteTotale();
        $nbTranches = 6;
        $montantTranche = (int) round($scolariteTotale / $nbTranches);
        $totalPaye = $paiements->where('statut', 'valide')->sum('montant');
        $progression = $scolariteTotale > 0 ? min(100, (int) round($totalPaye / $scolariteTotale * 100)) : 0;

        // Le solde affiché doit toujours refléter scolarité - paiements validés,
        // sinon il peut diverger de la colonne stockée (ex. désynchro lors d'un seed).
        $etudiant->solde = $etudiant->soldeReel();

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

        // Paiement mobile via PayDunya (Orange Money, Wave, Free Money)
        if (in_array($validated['mode_paiement'], ['orange_money', 'wave', 'free_money'])) {
            $paydunya = new PaydunyaService();
            $result = $paydunya->creerFacture($etudiant, (int) $validated['montant'], $validated['reference']);

            if ($result['success']) {
                // Sauvegarder le token PayDunya dans la session
                session(['paydunya_token' => $result['token'], 'paydunya_reference' => $validated['reference']]);
                return redirect()->away($result['url']);
            }

            return back()->withErrors(['paydunya' => 'Erreur PayDunya : '.$result['error']]);
        }

        // Paiement manuel (espèces, virement, chèque) → déclaration classique
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

        return back()->with('success', 'Déclaration envoyée. Elle sera validée par le service comptable sous 24h.');
    }

    public function retourPaydunya(Request $request): View|RedirectResponse
    {
        $token = $request->get('token') ?? session('paydunya_token');

        if (!$token) {
            return redirect()->route('etudiant.paiements.index')
                ->withErrors(['paydunya' => 'Token de paiement introuvable.']);
        }

        $paydunya  = new PaydunyaService();
        $result    = $paydunya->verifierPaiement($token);
        $etudiant  = auth()->user()->etudiant;
        $reference = session('paydunya_reference', 'PAY-'.strtoupper(uniqid()));

        if ($result['success'] && $result['statut'] === 'completed') {
            // Paiement confirmé par PayDunya → créer le paiement validé
            $paiement = Paiement::firstOrCreate(
                ['reference' => $reference],
                [
                    'etudiant_id'   => $etudiant->id,
                    'date_paiement' => now()->toDateString(),
                    'montant'       => $result['montant'],
                    'mode_paiement' => 'paydunya',
                    'statut'        => 'valide',
                    'valide_le'     => now(),
                ]
            );

            if ($paiement->wasRecentlyCreated) {
                $etudiant->decrement('solde', $result['montant']);
                $etudiant->refresh();

                $msgSolde = $etudiant->solde <= 0
                    ? 'Votre scolarité est entièrement réglée !'
                    : 'Solde restant : '.number_format($etudiant->solde, 0, ',', ' ').' FCFA.';

                $etudiant->user->notifications()->create([
                    'id'   => \Illuminate\Support\Str::uuid(),
                    'type' => 'App\\Notifications\\PaiementValide',
                    'data' => [
                        'titre'   => 'Paiement PayDunya confirmé — '.$reference,
                        'message' => 'Votre paiement de '.number_format($result['montant'], 0, ',', ' ').' FCFA via PayDunya a été confirmé. '.$msgSolde,
                        'recu_url'=> route('etudiant.paiements.recu', $paiement),
                    ],
                    'read_at' => null,
                ]);
            }

            session()->forget(['paydunya_token', 'paydunya_reference']);
            return redirect()->route('etudiant.paiements.recu', $paiement)
                ->with('success', 'Paiement PayDunya confirmé avec succès !');
        }

        return redirect()->route('etudiant.paiements.index')
            ->with('warning', 'Paiement en attente de confirmation PayDunya. Statut : '.($result['statut'] ?? 'inconnu'));
    }

    public function callbackPaydunya(Request $request): \Illuminate\Http\JsonResponse
    {
        // Webhook IPN PayDunya
        $token    = $request->get('data')['invoice']['token'] ?? null;
        $statut   = $request->get('data')['invoice']['status'] ?? null;

        if ($token && $statut === 'completed') {
            $paydunya = new PaydunyaService();
            $result   = $paydunya->verifierPaiement($token);

            if ($result['success']) {
                $custom    = $result['custom_data'];
                $etudiant  = \App\Models\Etudiant::find($custom['etudiant_id'] ?? null);
                $reference = $custom['reference'] ?? 'PAY-'.uniqid();

                if ($etudiant) {
                    Paiement::firstOrCreate(
                        ['reference' => $reference],
                        [
                            'etudiant_id'   => $etudiant->id,
                            'date_paiement' => now()->toDateString(),
                            'montant'       => $result['montant'],
                            'mode_paiement' => 'paydunya',
                            'statut'        => 'valide',
                            'valide_le'     => now(),
                        ]
                    );
                    $etudiant->decrement('solde', $result['montant']);
                }
            }
        }

        return response()->json(['status' => 'ok']);
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
