<?php

namespace App\Http\Controllers\Recouvrement;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use Illuminate\View\View;

class ImpayeController extends Controller
{
    public function index(): View
    {
        $impayes = Etudiant::with(['user', 'engagements'])
            ->where('solde', '>', 0)
            ->orderByDesc('solde')
            ->get();

        return view('recouvrement.impayes.index', [
            'impayes' => $impayes,
        ]);
    }
}
