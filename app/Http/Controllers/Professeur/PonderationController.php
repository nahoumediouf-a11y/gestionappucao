<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professeur\Concerns\InteractsWithEtudiants;
use App\Models\Note;
use App\Models\Ponderation;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PonderationController extends Controller
{
    use InteractsWithEtudiants;

    public function edit(Request $request): View
    {
        $filiere = (string) $request->query('filiere');
        $niveau = (string) $request->query('niveau');
        $matiere = (string) $request->query('matiere');

        abort_unless($this->enseigneClasse($filiere, $niveau), Response::HTTP_FORBIDDEN);

        return view('professeur.ponderation.edit', [
            'filiere' => $filiere,
            'niveau' => $niveau,
            'matiere' => $matiere,
            'ponderation' => Ponderation::pour($filiere, $niveau, $matiere),
            'categories' => Note::CATEGORIES,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'filiere' => ['required', 'string', 'max:255'],
            'niveau' => ['required', 'string', 'max:50'],
            'matiere' => ['required', 'string', 'max:255'],
            'poids_examen' => ['required', 'integer', 'min:0', 'max:100'],
            'poids_tp' => ['required', 'integer', 'min:0', 'max:100'],
            'poids_td' => ['required', 'integer', 'min:0', 'max:100'],
            'poids_cc' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        abort_unless($this->enseigneClasse($validated['filiere'], $validated['niveau']), Response::HTTP_FORBIDDEN);

        $somme = $validated['poids_examen'] + $validated['poids_tp'] + $validated['poids_td'] + $validated['poids_cc'];
        if ($somme !== 100) {
            return back()->withInput()->withErrors(['poids' => 'La somme des poids doit être égale à 100 % (actuellement '.$somme.' %).']);
        }

        Ponderation::updateOrCreate(
            ['filiere' => $validated['filiere'], 'niveau' => $validated['niveau'], 'matiere' => $validated['matiere']],
            [
                'professeur_id' => auth()->id(),
                'poids_examen' => $validated['poids_examen'],
                'poids_tp' => $validated['poids_tp'],
                'poids_td' => $validated['poids_td'],
                'poids_cc' => $validated['poids_cc'],
            ]
        );

        ActivityLogger::log('ponderation.update', "Pondération mise à jour : {$validated['matiere']} ({$validated['filiere']} {$validated['niveau']}).");

        return redirect()->route('professeur.carnet.index', [
            'filiere' => $validated['filiere'],
            'niveau' => $validated['niveau'],
            'matiere' => $validated['matiere'],
        ])->with('success', 'Pondération enregistrée.');
    }
}
