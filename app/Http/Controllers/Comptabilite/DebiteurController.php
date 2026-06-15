<?php

namespace App\Http\Controllers\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DebiteurController extends Controller
{
    public function index(Request $request): View
    {
        $debiteurs = Etudiant::with('user')
            ->where('solde', '>', 0)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q');
                $query->where(function ($q) use ($term) {
                    $q->where('matricule', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($u) => $u->where('nom', 'like', "%{$term}%")->orWhere('prenom', 'like', "%{$term}%"));
                });
            })
            ->orderByDesc('solde')
            ->paginate(15)
            ->withQueryString();

        return view('comptabilite.debiteurs.index', [
            'debiteurs' => $debiteurs,
            'q' => $request->string('q'),
        ]);
    }
}
