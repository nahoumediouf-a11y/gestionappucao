<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CoursEnLigneController as AdminCoursEnLigneController;
use App\Http\Controllers\Admin\EmploiDuTempsController as AdminEmploiDuTempsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\StatistiqueController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Comptabilite\DebiteurController;
use App\Http\Controllers\Comptabilite\PaiementController as ComptabilitePaiementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Etudiant\AbsenceController as EtudiantAbsenceController;
use App\Http\Controllers\Etudiant\BulletinController;
use App\Http\Controllers\Etudiant\CoursEnLigneController as EtudiantCoursEnLigneController;
use App\Http\Controllers\Etudiant\DocumentController as EtudiantDocumentController;
use App\Http\Controllers\Etudiant\DocumentCoursController as EtudiantDocumentCoursController;
use App\Http\Controllers\Etudiant\EmploiDuTempsController as EtudiantEmploiDuTempsController;
use App\Http\Controllers\Etudiant\NoteController as EtudiantNoteController;
use App\Http\Controllers\Etudiant\PaiementController as EtudiantPaiementController;
use App\Http\Controllers\Etudiant\ProfilController;
use App\Http\Controllers\Etudiant\ProjetController as EtudiantProjetController;
use App\Http\Controllers\Etudiant\PropositionProjetController;
use App\Http\Controllers\Financier\PaiementController as FinancierPaiementController;
use App\Http\Controllers\Financier\RapportController;
use App\Http\Controllers\Financier\StatistiqueController as FinancierStatistiqueController;
use App\Http\Controllers\NotificationController as UserNotificationController;
use App\Http\Controllers\Professeur\AbsenceController as ProfesseurAbsenceController;
use App\Http\Controllers\Professeur\CoursEnLigneController as ProfesseurCoursEnLigneController;
use App\Http\Controllers\Professeur\DocumentController as ProfesseurDocumentController;
use App\Http\Controllers\Professeur\DocumentCoursController as ProfesseurDocumentCoursController;
use App\Http\Controllers\Professeur\EmploiDuTempsController as ProfesseurEmploiDuTempsController;
use App\Http\Controllers\Professeur\EtudiantController as ProfesseurEtudiantController;
use App\Http\Controllers\Professeur\NoteController as ProfesseurNoteController;
use App\Http\Controllers\Professeur\ProjetController as ProfesseurProjetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Recouvrement\AJourController;
use App\Http\Controllers\Recouvrement\EngagementController;
use App\Http\Controllers\Recouvrement\ImpayeController;
use App\Http\Controllers\Recouvrement\RechercheController;
use App\Http\Controllers\Recouvrement\RelanceController;
use App\Http\Controllers\Recouvrement\StatistiqueController as RecouvrementStatistiqueController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showWelcome'])->name('welcome');
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');

    // Inscription désactivée : la création de compte est réservée à l'administration.
    // Route::get('/inscription', [RegisterController::class, 'showRegisterForm'])->name('register');
    // Route::post('/inscription', [RegisterController::class, 'register'])->middleware('throttle:5,1');

    Route::get('/mot-de-passe-oublie', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [ForgotPasswordController::class, 'send'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [ForgotPasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/assistant', [AssistantController::class, 'index'])->name('assistant.index');
    Route::post('/assistant', [AssistantController::class, 'ask'])->name('assistant.ask');

    Route::get('/mot-de-passe', [ProfileController::class, 'edit'])->name('profile.password.edit');
    Route::put('/mot-de-passe', [ProfileController::class, 'update'])->name('profile.password.update');

    // ===== Administrateur =====
    Route::middleware('role:administrateur')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('utilisateurs', UserController::class)->except('show');
        Route::patch('utilisateurs/{utilisateur}/activer', [UserController::class, 'activer'])->name('utilisateurs.activer');
        Route::get('statistiques', [StatistiqueController::class, 'index'])->name('statistiques');
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('notifications/{notification}/lue', [NotificationController::class, 'read'])->name('notifications.read');

        Route::get('recherche', [RechercheController::class, 'index'])->name('recherche.index');
        Route::get('journal-activites', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('emploi-du-temps', [AdminEmploiDuTempsController::class, 'index'])->name('emploi-du-temps.index');
        Route::get('emploi-du-temps/creer', [AdminEmploiDuTempsController::class, 'create'])->name('emploi-du-temps.create');
        Route::post('emploi-du-temps', [AdminEmploiDuTempsController::class, 'store'])->name('emploi-du-temps.store');
        Route::get('emploi-du-temps/{creneau}/modifier', [AdminEmploiDuTempsController::class, 'edit'])->name('emploi-du-temps.edit');
        Route::put('emploi-du-temps/{creneau}', [AdminEmploiDuTempsController::class, 'update'])->name('emploi-du-temps.update');
        Route::delete('emploi-du-temps/{creneau}', [AdminEmploiDuTempsController::class, 'destroy'])->name('emploi-du-temps.destroy');

        Route::get('cours-en-ligne', [AdminCoursEnLigneController::class, 'index'])->name('cours-en-ligne.index');
        Route::patch('cours-en-ligne/{cours}/annuler', [AdminCoursEnLigneController::class, 'annuler'])->name('cours-en-ligne.annuler');
    });

    // ===== Agent comptable =====
    Route::middleware('role:agent_comptable')->prefix('comptabilite')->name('comptabilite.')->group(function () {
        Route::get('paiements', [ComptabilitePaiementController::class, 'index'])->name('paiements.index');
        Route::get('paiements/creer', [ComptabilitePaiementController::class, 'create'])->name('paiements.create');
        Route::post('paiements', [ComptabilitePaiementController::class, 'store'])->name('paiements.store');
        Route::get('paiements/{paiement}/modifier', [ComptabilitePaiementController::class, 'edit'])->name('paiements.edit');
        Route::put('paiements/{paiement}', [ComptabilitePaiementController::class, 'update'])->name('paiements.update');
        Route::get('paiements/{paiement}/recu', [ComptabilitePaiementController::class, 'recu'])->name('paiements.recu');
        Route::patch('paiements/{paiement}/valider', [ComptabilitePaiementController::class, 'valider'])->name('paiements.valider');
        Route::patch('paiements/{paiement}/rejeter', [ComptabilitePaiementController::class, 'rejeter'])->name('paiements.rejeter');
        Route::get('debiteurs', [DebiteurController::class, 'index'])->name('debiteurs.index');
    });

    // ===== Agent de recouvrement =====
    Route::middleware('role:agent_recouvrement')->prefix('recouvrement')->name('recouvrement.')->group(function () {
        Route::get('impayes', [ImpayeController::class, 'index'])->name('impayes.index');
        Route::get('a-jour', [AJourController::class, 'index'])->name('ajour.index');
        Route::get('recherche', [RechercheController::class, 'index'])->name('recherche.index');
        Route::resource('engagements', EngagementController::class)->except(['destroy']);
        Route::get('relances', [RelanceController::class, 'index'])->name('relances.index');
        Route::post('relances/{engagement}', [RelanceController::class, 'relancer'])->name('relances.relancer');
        Route::get('statistiques', [RecouvrementStatistiqueController::class, 'index'])->name('statistiques');
    });

    // ===== Responsable financier =====
    Route::middleware('role:responsable_financier')->prefix('financier')->name('financier.')->group(function () {
        Route::get('paiements', [FinancierPaiementController::class, 'index'])->name('paiements.index');
        Route::patch('paiements/{paiement}/valider', [FinancierPaiementController::class, 'valider'])->name('paiements.valider');
        Route::get('rapports', [RapportController::class, 'index'])->name('rapports.index');
        Route::get('rapports/telecharger', [RapportController::class, 'telecharger'])->name('rapports.telecharger');
        Route::get('statistiques', [FinancierStatistiqueController::class, 'index'])->name('statistiques');
    });

    // ===== Étudiant =====
    Route::middleware('role:etudiant')->prefix('etudiant')->name('etudiant.')->group(function () {
        Route::get('notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('notifications/{notification}/lue', [UserNotificationController::class, 'read'])->name('notifications.read');
        Route::get('profil', [ProfilController::class, 'index'])->name('profil.index');
        Route::put('profil/contact-urgence', [ProfilController::class, 'updateContactUrgence'])->name('profil.contact-urgence.update');
        Route::get('notes', [EtudiantNoteController::class, 'index'])->name('notes.index');
        Route::get('bulletin', [BulletinController::class, 'index'])->name('bulletin.index');
        Route::get('bulletin/pdf', [BulletinController::class, 'telecharger'])->name('bulletin.pdf');
        Route::get('absences', [EtudiantAbsenceController::class, 'index'])->name('absences.index');
        Route::get('emploi-du-temps', [EtudiantEmploiDuTempsController::class, 'index'])->name('edt.index');
        Route::get('emploi-du-temps/pdf', [EtudiantEmploiDuTempsController::class, 'pdf'])->name('edt.pdf');
        Route::get('cours-en-ligne', [EtudiantCoursEnLigneController::class, 'index'])->name('cours.index');
        Route::get('cours-en-ligne/{cours}/salle', [EtudiantCoursEnLigneController::class, 'salle'])->name('cours.salle');
        Route::get('paiements', [EtudiantPaiementController::class, 'index'])->name('paiements.index');
        Route::post('paiements', [EtudiantPaiementController::class, 'store'])->name('paiements.store');
        Route::get('paiements/{paiement}/recu', [EtudiantPaiementController::class, 'recu'])->name('paiements.recu');
        Route::get('paiements/retour', [EtudiantPaiementController::class, 'retourPaydunya'])->name('paiements.paydunya.retour');
        Route::post('paiements/callback', [EtudiantPaiementController::class, 'callbackPaydunya'])->name('paiements.paydunya.callback')->withoutMiddleware(['auth', 'role:etudiant']);
        Route::get('projets', [EtudiantProjetController::class, 'index'])->name('projets.index');
        Route::get('projets/{projet}', [EtudiantProjetController::class, 'show'])->name('projets.show');
        Route::post('projets/{projet}/rendre', [EtudiantProjetController::class, 'soumettre'])->name('projets.soumettre');
        Route::get('projets/{projet}/fichier', [EtudiantProjetController::class, 'telecharger'])->name('projets.fichier');
        Route::get('propositions', [PropositionProjetController::class, 'index'])->name('propositions.index');
        Route::get('propositions/soumettre', [PropositionProjetController::class, 'create'])->name('propositions.create');
        Route::post('propositions', [PropositionProjetController::class, 'store'])->name('propositions.store');
        Route::get('documents', [EtudiantDocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/{document}/telecharger', [EtudiantDocumentController::class, 'download'])->name('documents.download');
        Route::get('documents-cours', [EtudiantDocumentCoursController::class, 'index'])->name('documents_cours.index');
        Route::get('documents-cours/{document}/telecharger', [EtudiantDocumentCoursController::class, 'telecharger'])->name('documents_cours.telecharger');
    });

    // ===== Professeur =====
    Route::middleware('role:professeur')->prefix('professeur')->name('professeur.')->group(function () {
        Route::get('notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('notifications/{notification}/lue', [UserNotificationController::class, 'read'])->name('notifications.read');
        Route::get('emploi-du-temps', [ProfesseurEmploiDuTempsController::class, 'index'])->name('edt.index');
        Route::get('emploi-du-temps/pdf', [ProfesseurEmploiDuTempsController::class, 'pdf'])->name('edt.pdf');

        Route::get('cours-en-ligne', [ProfesseurCoursEnLigneController::class, 'index'])->name('cours.index');
        Route::get('cours-en-ligne/creer', [ProfesseurCoursEnLigneController::class, 'create'])->name('cours.create');
        Route::post('cours-en-ligne', [ProfesseurCoursEnLigneController::class, 'store'])->name('cours.store');
        Route::get('cours-en-ligne/{cours}/modifier', [ProfesseurCoursEnLigneController::class, 'edit'])->name('cours.edit');
        Route::put('cours-en-ligne/{cours}', [ProfesseurCoursEnLigneController::class, 'update'])->name('cours.update');
        Route::delete('cours-en-ligne/{cours}', [ProfesseurCoursEnLigneController::class, 'destroy'])->name('cours.destroy');
        Route::post('cours-en-ligne/{cours}/demarrer', [ProfesseurCoursEnLigneController::class, 'demarrer'])->name('cours.demarrer');
        Route::post('cours-en-ligne/{cours}/terminer', [ProfesseurCoursEnLigneController::class, 'terminer'])->name('cours.terminer');
        Route::get('cours-en-ligne/{cours}/salle', [ProfesseurCoursEnLigneController::class, 'salle'])->name('cours.salle');
        Route::get('etudiants', [ProfesseurEtudiantController::class, 'index'])->name('etudiants.index');
        Route::get('notes', [ProfesseurNoteController::class, 'index'])->name('notes.index');
        Route::get('notes/creer', [ProfesseurNoteController::class, 'create'])->name('notes.create');
        Route::post('notes', [ProfesseurNoteController::class, 'store'])->name('notes.store');
        Route::get('notes/{note}/modifier', [ProfesseurNoteController::class, 'edit'])->name('notes.edit');
        Route::put('notes/{note}', [ProfesseurNoteController::class, 'update'])->name('notes.update');
        Route::get('absences', [ProfesseurAbsenceController::class, 'index'])->name('absences.index');
        Route::get('absences/creer', [ProfesseurAbsenceController::class, 'create'])->name('absences.create');
        Route::post('absences', [ProfesseurAbsenceController::class, 'store'])->name('absences.store');
        Route::get('absences/{absence}/modifier', [ProfesseurAbsenceController::class, 'edit'])->name('absences.edit');
        Route::put('absences/{absence}', [ProfesseurAbsenceController::class, 'update'])->name('absences.update');
        Route::get('propositions', [App\Http\Controllers\Professeur\PropositionProjetController::class, 'index'])->name('propositions.index');
        Route::patch('propositions/{proposition}/traiter', [App\Http\Controllers\Professeur\PropositionProjetController::class, 'traiter'])->name('propositions.traiter');
        Route::get('documents-cours', [ProfesseurDocumentCoursController::class, 'index'])->name('documents_cours.index');
        Route::get('documents-cours/ajouter', [ProfesseurDocumentCoursController::class, 'create'])->name('documents_cours.create');
        Route::post('documents-cours', [ProfesseurDocumentCoursController::class, 'store'])->name('documents_cours.store');
        Route::delete('documents-cours/{document}', [ProfesseurDocumentCoursController::class, 'destroy'])->name('documents_cours.destroy');
        Route::get('projets', [ProfesseurProjetController::class, 'index'])->name('projets.index');
        Route::get('projets/creer', [ProfesseurProjetController::class, 'create'])->name('projets.create');
        Route::post('projets', [ProfesseurProjetController::class, 'store'])->name('projets.store');
        Route::get('projets/{projet}/modifier', [ProfesseurProjetController::class, 'edit'])->name('projets.edit');
        Route::put('projets/{projet}', [ProfesseurProjetController::class, 'update'])->name('projets.update');
        Route::delete('projets/{projet}', [ProfesseurProjetController::class, 'destroy'])->name('projets.destroy');
        Route::get('projets/{projet}/copies', [ProfesseurProjetController::class, 'soumissions'])->name('projets.soumissions');
        Route::get('projets/{projet}/copies/export', [ProfesseurProjetController::class, 'exportCsv'])->name('projets.export');
        Route::get('projets/{projet}/copies/{soumission}/fichier', [ProfesseurProjetController::class, 'telecharger'])->name('projets.copie.fichier');
        Route::post('projets/{projet}/copies/{soumission}/corriger', [ProfesseurProjetController::class, 'corriger'])->name('projets.corriger');
        Route::get('documents', [ProfesseurDocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/ajouter', [ProfesseurDocumentController::class, 'create'])->name('documents.create');
        Route::post('documents', [ProfesseurDocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/{document}/telecharger', [ProfesseurDocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [ProfesseurDocumentController::class, 'destroy'])->name('documents.destroy');
    });
});
