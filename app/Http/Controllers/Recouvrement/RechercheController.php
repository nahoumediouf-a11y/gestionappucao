<?php

namespace App\Http\Controllers\Recouvrement;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RechercheController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q');

        $resultats = collect();

        if ($q->isNotEmpty()) {
            $resultats = Etudiant::with(['user', 'paiements', 'engagements'])
                ->where('matricule', 'like', "%{$q}%")
                ->orWhereHas('user', fn ($u) => $u->where('nom', 'like', "%{$q}%")->orWhere('prenom', 'like', "%{$q}%"))
                ->get();
        }

        return view('recouvrement.recherche.index', [
            'q' => $q,
            'resultats' => $resultats,
        ]);
    }
}
