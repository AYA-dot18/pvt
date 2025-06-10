<?php

use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\MaeController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\PrimeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TgrController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use ArPHP\I18N\Arabic;
use App\Http\Controllers\LogController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\GroupTripController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// routes/web.php
Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluation.index');
Route::post('/evaluations', [EvaluationController::class, 'store'])->name('evaluation.store');
Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluation.index');
Route::get('/evaluations', [EvaluationController::class, 'create'])->name('evaluation.create');
Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluation.Liste');
Route::get('/evaluations/create', [EvaluationController::class, 'index'])->name('evaluation.index');
Route::post('/evaluations', [EvaluationController::class, 'store'])->name('evaluation.store');
Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluation.Liste');

Route::get('/evaluations/{evaluation}', [EvaluationController::class, 'show'])->name('evaluation.show');
Route::get('/evaluations/{evaluation}/edit', [EvaluationController::class, 'edit'])->name('evaluation.edit');
Route::put('/evaluations/{evaluation}', [EvaluationController::class, 'update'])->name('evaluation.update');
Route::delete('/evaluations/{evaluation}', [EvaluationController::class, 'destroy'])->name('evaluation.destroy');

// EvaluationController.php
Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluation.index'); 
// Cette route affiche le formulaire dans Index.vue

Route::post('/evaluations', [EvaluationController::class, 'store'])->name('evaluation.store');

Route::get('/evaluations/liste', [EvaluationController::class, 'liste'])->name('evaluation.liste'); 
// Cette route affiche la liste des évaluations dans Liste.vue


Route::get('/evaluations', [EvaluationController::class, 'index2'])->name('evaluation.index2'); // liste affichée
Route::post('/evaluations', [EvaluationController::class, 'store'])->name('evaluation.store');


Route::get('/', function () {
    return Inertia::render('Test');
});

Route::get('/personnels', [PersonnelController::class, 'index']);
Route::get('/personnels/{id}/primes', [PersonnelController::class, 'primes']);
Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');




Route::resource('group-trips', GroupTripController::class);
Route::post('group-trips/{id}/add-participant', [GroupTripController::class, 'addParticipant'])->name('group-trips.addParticipant');
Route::get('group-trips/{id}/calculate-primes', [GroupTripController::class, 'calculatePrimes'])->name('group-trips.calculatePrimes');



Route::post('/calculer-primes', [PrimeController::class, 'calculerPrimes']);
Route::get('group-trips/{id}', [GroupTripController::class, 'show'])->name('group-trips.show');

Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
Route::resource('group-trips', GroupTripController::class);
Route::post('group-trips/{id}/add-participant', [GroupTripController::class, 'addParticipant'])->name('group-trips.addParticipant');
Route::get('group-trips/{id}/calculate-primes', [GroupTripController::class, 'calculatePrimes'])->name('group-trips.calculatePrimes');


Route::redirect('/', '/login');
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::get('/personnels/fetch', [PersonnelController::class, 'fetch']);
    Route::post('/personnels/{personnel}/toggle',[PersonnelController::class, 'toggle'])->name('personnels.toggle');
    Route::get('/personnels/{personnel}/ik',[PersonnelController::class, 'ikdownload'])->name('personnels.ik.download');
    Route::resource('/personnels',PersonnelController::class);
    Route::get('/conges/{personnel}/fetch', [CongeController::class, 'fetch']);
    Route::resource('/conges',CongeController::class);
    Route::get('/maes/{personnel}/fetch', [MaeController::class, 'fetch']);
    Route::resource('/maes',MaeController::class);
    Route::get('/primes/{personnel}/fetch', [PrimeController::class, 'fetch']);
    Route::resource('/primes',PrimeController::class);
    Route::get('/tgrs/fetch/deplacements',[TgrController::class, 'fetchdeplacements']);
    Route::get('/tgrs/download',[TgrController::class, 'download'])->name('tgrs.download');
    Route::get('/tgrs/historique',[TgrController::class, 'historique'])->name('tgrs.historique');
    Route::resource('/tgrs',TgrController::class);
    Route::get('/settings',[SettingController::class, 'index'])->name('settings.index');
    Route::get('/settings/joursferies/fetch', [SettingController::class, 'joursferiesfetch']);
    Route::post('/settings/joursferies', [SettingController::class, 'joursferiesstore'])->name('settings.joursferies.store');
    Route::put('/settings/joursferies/{JF}', [SettingController::class, 'joursferiesupdate'])->name('settings.joursferies.update');
    Route::delete('/settings/joursferies/destroy', [SettingController::class, 'joursferiesdestroy'])->name('settings.joursferies.destroy');
    Route::get('/settings/lignesbudgetaires/fetch', [SettingController::class, 'lignesbudgetairesfetch']);
    Route::post('/settings/lignesbudgetaires', [SettingController::class, 'lignesbudgetairesstore'])->name('settings.lignesbudgetaires.store');
    Route::put('/settings/lignesbudgetaires/{LB}', [SettingController::class, 'lignesbudgetairesupdate'])->name('settings.lignesbudgetaires.update');
    Route::delete('/settings/lignesbudgetaires/destroy', [SettingController::class, 'lignesbudgetairesdestroy'])->name('settings.lignesbudgetaires.destroy');
    Route::get('/pdf/mission', function () {
        $data=[
            'exercice' => '2025',
            'chapitre' => '1212018000', // ligne Budgétaire chapitre (les deplacements doivent être groupé selon la même ligne budgétaire)
            'article' => '15421',// ligne Budgétaire article
            'paragraphe' => '20',// ligne Budgétaire paragraphe
            'ligne' => '64',// ligne Budgétaire ligne
            'cin' => 'sdsd',
            'rib' => '54564151615',
            'fullname' => "Lamane Mohamed Amine", // fullname du personnel
            'grade' => "Ingénieur d'état 1er grade",//grade du personnel
            'echelle' => '11',// echelle du personnel
            'ddr' => '2958475', // num_ppr du personnel
            'mois' => "Janvier", // le mois des déplacements (les deplacements doivent être groupé selon le même mois) le mois de déplacement est le mois de date_debut ou date_fin puisque il sont toujours dans le même mois.
            'deplacements' =>[],
            'montant' => "564456",
            'montant_text' => "c'est la somme des déplacements",
        ];
        $reportHtml = view('pdf.etatsomme_ik',$data)->render();
        
        $arabic = new Arabic();
        $p = $arabic->arIdentify($reportHtml);

        for ($i = count($p)-1; $i >= 0; $i-=2) {
            $utf8ar = $arabic->utf8Glyphs(substr($reportHtml, $p[$i-1], $p[$i] - $p[$i-1]));
            $reportHtml = substr_replace($reportHtml, $utf8ar, $p[$i-1], $p[$i] - $p[$i-1]);
        }
        $pdf = PDF::loadHTML($reportHtml);
        return $pdf->stream();
        // $pdf = PDF::loadView('pdf.etatsomme_ik',$data);
        // return $pdf->stream();
    });
});