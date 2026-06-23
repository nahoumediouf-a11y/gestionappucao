<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\EmploiDuTemps;
use App\Models\User;
use App\Notifications\SalleModifieeNotification;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmploiDuTempsController extends Controller
{
    public function index(Request $request): View
    {
        $creneaux = EmploiDuTemps::with('professeur')
            ->when($request->filled('filiere'), fn ($q) => $q->where('filiere', $request->string('filiere')))
            ->when($request->filled('niveau'), fn ($q) => $q->where('niveau', $request->string('niveau')))
            ->orderBy('filiere')
            ->orderBy('niveau')
            ->orderBy('jour')
            ->orderBy('heure_debut')
            ->paginate(20)
            ->withQueryString();

        return view('admin.emploi-du-temps.index', [
            'creneaux' => $creneaux,
            'filiere' => $request->string('filiere'),
            'niveau' => $request->string('niveau'),
        ]);
    }

    public function create(): View
    {
        return view('admin.emploi-du-temps.create', [
            'professeurs' => User::where('role', Role::Professeur)->orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCreneau($request);

        $conflits = EmploiDuTemps::detecterConflits($validated);
        if ($conflits) {
            return back()->withInput()->withErrors(['conflit' => $conflits]);
        }

        EmploiDuTemps::create($validated);

        ActivityLogger::log('edt.create', "Création d'un créneau d'emploi du temps : {$validated['matiere']} ({$validated['filiere']} {$validated['niveau']}, {$validated['jour']}).");

        return redirect()->route('admin.emploi-du-temps.index')->with('success', 'Créneau ajouté avec succès.');
    }

    public function edit(EmploiDuTemps $creneau): View
    {
        return view('admin.emploi-du-temps.edit', [
            'creneau' => $creneau,
            'professeurs' => User::where('role', Role::Professeur)->orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, EmploiDuTemps $creneau): RedirectResponse
    {
        $validated = $this->validateCreneau($request);

        $conflits = EmploiDuTemps::detecterConflits($validated, $creneau->id);
        if ($conflits) {
            return back()->withInput()->withErrors(['conflit' => $conflits]);
        }

        $ancienneSalle = $creneau->salle;

        $creneau->update($validated);

        if ($ancienneSalle !== $validated['salle']) {
            $this->notifierChangementSalle($creneau, $ancienneSalle);
        }

        ActivityLogger::log('edt.update', "Modification du créneau {$creneau->matiere} ({$creneau->filiere} {$creneau->niveau}, {$creneau->jour}).");

        return redirect()->route('admin.emploi-du-temps.index')->with('success', 'Créneau modifié avec succès.');
    }

    public function destroy(EmploiDuTemps $creneau): RedirectResponse
    {
        $creneau->delete();

        ActivityLogger::log('edt.delete', "Suppression d'un créneau d'emploi du temps.");

        return redirect()->route('admin.emploi-du-temps.index')->with('success', 'Créneau supprimé avec succès.');
    }

    private function notifierChangementSalle(EmploiDuTemps $creneau, string $ancienneSalle): void
    {
        $notification = new SalleModifieeNotification($creneau, $ancienneSalle);

        $etudiants = User::where('role', Role::Etudiant)
            ->whereHas('etudiant', function ($q) use ($creneau) {
                $q->where('filiere', $creneau->filiere)->where('niveau', $creneau->niveau);
            })
            ->get();

        foreach ($etudiants as $etudiant) {
            $etudiant->notify($notification);
        }

        $creneau->professeur?->notify($notification);
    }

    private function validateCreneau(Request $request): array
    {
        return $request->validate([
            'filiere' => ['required', 'string', 'max:255'],
            'niveau' => ['required', 'string', 'max:50'],
            'jour' => ['required', 'string', 'in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'matiere' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(EmploiDuTemps::TYPES))],
            'salle' => ['required', 'string', 'max:50'],
            'professeur_id' => ['nullable', 'exists:users,id'],
        ]);
    }
}
