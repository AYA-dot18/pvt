<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\Vehicule;
use App\Models\Prime;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\PersonnelStoreRequest;
use App\Http\Requests\PersonnelUpdateRequest;
use App\Models\MoisPvt;
use App\Models\LigneBudgetaire;
use App\Models\Trajet;
use App\Models\Conge;
use App\Models\Mae;
use App\Models\Deplacement;
use App\Models\JoursFeries;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use NumberFormatter;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Facades\LogBatch; 
use Illuminate\Support\Facades\Auth;

class PersonnelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Personnel/Index');
    }
     public function primes($id)
    {
        $personnel = Personnel::findOrFail($id);
        return $personnel->primes()->select('id', 'type')->get();
    }

    /**
     * Return data to vue as json
     */
    public function fetch(Request $request)
    {
        $total = Personnel::count();
        $query = Personnel::query()->with('vehicule');
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('num_ppr', 'like', "%{$search}%")
                  ->orWhere('num_cin', 'like', "%{$search}%")
                  ->orWhere('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%");
            }); 
        }
        if ($request->has('sortBy')) {
            $sort = $request->get('sortBy');
            $sortBy = $sort[0]['key'];
            $sortOrder= $sort[0]['order'];
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Default sorting by id if no specific sorting is requested
            $query->orderBy('id', 'ASC');
        }
        $itemsPerPage = $request->input('itemsPerPage');
        if($itemsPerPage >= 1){
            $items = $query->paginate($request->input('itemsPerPage'));
            }
        if(!$itemsPerPage){
            $items = $query->paginate(10);
        }
        if($itemsPerPage == -1){
            $items = $query->paginate($total);
        }
       activity()
    ->causedBy(Auth::user())
    ->withProperties([
        'id_personnel' => null,
        'ancienne_valeur' => null,
        'nouvelle_valeur' => null,
    ])
    ->log('Consultation de la liste du personnel');


        return response()->json([
            'personnels' => $items,
            'total' => $items->total(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Personnel/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PersonnelStoreRequest $request)
    {
        // Validation des données via PersonnelStoreRequest
        $validatedData = $request->validated();

        // Création d'une nouvelle instance de Personnel
        $personnel = new Personnel();

        // Remplissage des attributs principaux
        $personnel->nom = $validatedData['nom'];
        $personnel->prenom = $validatedData['prenom'];
        $personnel->num_cin = $validatedData['num_cin'];
        $personnel->num_ppr = $validatedData['num_ppr'];
        $personnel->grade = $validatedData['grade'];
        $personnel->echelle = $validatedData['echelle'];
        $personnel->groupe = $validatedData['groupe'];
        $personnel->taux_indemnite = $validatedData['taux_indemnite'];
        $personnel->montant_indemnite = $validatedData['montant_indemnite'];
        $personnel->banque_rib = $validatedData['banque_rib'];
        $personnel->guichet_rib = $validatedData['guichet_rib'];
        $personnel->num_compte_rib = $validatedData['num_compte_rib'];
        $personnel->code_rib = $validatedData['code_rib'];
        $personnel->residence = $validatedData['residence'];
        $personnel->statut = 1;
        $personnel->creance = $validatedData['creance'];
        $personnel->suffix = $validatedData['suffix'];
        $personnel->situation_familiale = $validatedData['situation_familiale'];

        $personnel->save();
        // Gestion des informations sur le véhicule, si elles sont présentes
        if (isset($validatedData['vehicule']['nom'])) {
            $vehiculeData = $validatedData['vehicule'];
            $vehicule = new Vehicule();
            $vehicule->nom = $validatedData['vehicule']['nom'] ?? null;
            $vehicule->matricule = $validatedData['vehicule']['matricule'] ?? null;
            $vehicule->puissance = $validatedData['vehicule']['puissance'] ?? null;
            $vehicule->limite_annuel = $validatedData['vehicule']['limite_annuel'] ?? null;

            $personnel->vehicule()->save($vehicule);
        }
        activity()
            ->causedBy(Auth::user())
            ->performedOn($personnel)
            ->withProperties([
                 'id_personnel' => $personnel->id,
                'nom_complet' => $personnel->nom . ' ' . $personnel->prenom,
                'ancienne_valeur' => null,
                'nouvelle_valeur' => ['id_personnel' => $personnel->personnel_id], 
            ])
        ->log('Ajout d\'un personnel');
        // Enregistrement dans la base de données
        return Redirect::route('personnels.index')->with('success',' Personnel ajouté avec succès!');
        
    }

    public function ikdownload(Request $request, Personnel $personnel)
    {
        $moisDeplacement_lettre = ucfirst(Carbon::createFromDate(null, (int) $request->month, 1)->translatedFormat('F'));
        $deplacements = [];
        $montant = 0;
        $month = $request->month;
        $year = $request->year;  
        $ligne_budgetaire_id = $request->ligne_budgetaire_id;
        foreach ($personnel->primes as $prime) {
            foreach ($prime->deplacements as $deplacement) {
                if (
                    $deplacement->ik &&
                    $deplacement->ik->ligne_budgetaire_id == $request->ligne_budgetaire_id &&
                    $deplacement->date_debut->format('n') == $request->month && 
                    $deplacement->date_debut->format('Y') == $request->year
                ) {
                    $deplacement->load("trajet");
                    $deplacements[] = $deplacement;
                    
                    $montant += $deplacement->ik->montant;
                }
            }
        }
        $ligneBudgetaire = LigneBudgetaire::find($ligne_budgetaire_id);
         // Récupérer les informations du véhicule
         $vehicule = $personnel->vehicule?->nom 
         ? "Personnel ".$personnel->vehicule->nom."      ".$personnel->vehicule->matricule."     ".$personnel->vehicule->puissance." CH"
         : '';
         $parts = explode('.', $montant);
        // Initialiser les variables
        $partie_entiere = $parts[0];
        $partie_decimale = isset($parts[1]) ? str_pad($parts[1], 2, '0', STR_PAD_RIGHT) : null;
        $entiere_text = ucwords($this->nombreEnLettres($partie_entiere));
        $decimale_text = $partie_decimale ? 'et '. ucwords($this->nombreEnLettres($partie_decimale)) . ' Centimes' : '';
        $total_text = $entiere_text.' Dirhams'. $decimale_text;
        $data = [
            'month' => $moisDeplacement_lettre,
            'year' => $request->year,
            'exercice' => $ligneBudgetaire->exercice,
            'chapitre' => $ligneBudgetaire->chapitre,
            'programme' => $ligneBudgetaire->programme,
            'region' => $ligneBudgetaire->region,
            'projet' => $ligneBudgetaire->projet,
            'ligne' => $ligneBudgetaire->ligne,
            'fullname' => $personnel->nom." ". $personnel->prenom,
            'grade' => $personnel->grade,
            'fonction' => 'XXXXXXXXXXX', // $personnel->fonction,
            'echelle' => $personnel->echelle,
            'residence' => $personnel->residence,
            'vehicule' => $vehicule,
            'mois' => $moisDeplacement_lettre,
            'deplacements' => $deplacements,
            'montant' => number_format(round($montant, 2), 2, ',', ' '),
            'montant_text' => $total_text
        ];
        $reportHtml = view('pdf.etatsomme_ik',$data)->render();
        
        $arabic = new Arabic();
        $p = $arabic->arIdentify($reportHtml);

        for ($i = count($p)-1; $i >= 0; $i-=2) {
            $utf8ar = $arabic->utf8Glyphs(substr($reportHtml, $p[$i-1], $p[$i] - $p[$i-1]));
            $reportHtml = substr_replace($reportHtml, $utf8ar, $p[$i-1], $p[$i] - $p[$i-1]);
        }
        

        $pdf = PDF::loadHTML($reportHtml);
        

activity()
    ->causedBy(Auth::user())
    ->performedOn($personnel)
    ->withProperties([
        'id_personnel' => $personnel->id,
        'nom_complet' => $personnel->nom . ' ' . $personnel->prenom,
    ])
    ->log('Téléchargement de l’état de somme IK');


        return $pdf->stream();
    }

    private function nombreEnLettres($nombre)
    {
        $formatter = new NumberFormatter('fr_FR', NumberFormatter::SPELLOUT);
    
        return $formatter->format($nombre);
    }
    /**
     * Display the specified resource.
     */
    public function show(Personnel $personnel)
    {   
        $currentYear = now()->year;
        $lignes_budgetaires = LigneBudgetaire::all();
        $trajets = Trajet::all();
        $personnel->load([
            'vehicule',
            'primes',
        ]);
        $moisPvtArray = MoisPvt::whereHas('prime', function ($query) use ($personnel) {
            $query->where('personnel_id', $personnel->id);
        })
        ->where('annee', $currentYear)
        ->pluck('mois')
        ->toArray();
        $Ikavailablemonths= [];
        foreach ($personnel->primes as $prime) {
            foreach ($prime->deplacements as $deplacement) {
                
                if ($deplacement->ik) {
                    $month = $deplacement->date_debut->format('n');
                    $year = $deplacement->date_debut->format('Y');
                    $ligne_budgetaire_id = $deplacement->ik->ligne_budgetaire_id;
                    // Vérifie si cette combinaison mois/année existe déjà
                    
                    $alreadyExists = collect($Ikavailablemonths)->contains(function ($item) use ($month, $year, $ligne_budgetaire_id) {
                        return $item['month'] == $month && $item['year'] == $year && $item['ligne_budgetaire_id'] == $ligne_budgetaire_id;
                    });
        
                    if (!$alreadyExists) {
                        $Ikavailablemonths[] = ['month' => $month, 'year' => $year, 'ligne_budgetaire_id' => $ligne_budgetaire_id];
                    }
                }
            }
        }
        $dates = collect();
        $startDates = collect();

        // Get all dates from Conges
        $conges = Conge::where('personnel_id', $personnel->id)->get();
        foreach ($conges as $conge) {
            $startDate = Carbon::parse($conge->date_debut);
            $endDate = Carbon::parse($conge->date_fin);

            if (!empty($conge->date_debut)) {
                $startDates->push(Carbon::parse($conge->date_debut)->toDateString());
            }

            if ($startDate && $endDate && $startDate->lte($endDate)) {
                $period = CarbonPeriod::create($startDate, $endDate);
                foreach ($period as $date) {
                    $dates->push($date->toDateString());
                }
            }
        }

        // Get all dates from Maes
        $maes = Mae::where('personnel_id', $personnel->id)->get();
        
        foreach ($maes as $mae) {
            $startDate = Carbon::parse($mae->date_depart);
            $endDate = Carbon::parse($mae->date_retour);

            if (!empty($mae->date_depart)) {
                $startDates->push(Carbon::parse($mae->date_depart)->toDateString());
            }

            if ($startDate && $endDate && $startDate->lte($endDate)) {
                $period = CarbonPeriod::create($startDate, $endDate);
                foreach ($period as $date) {
                    $dates->push($date->toDateString());
                }
            }
        }
        $primes = Prime::where('personnel_id', $personnel->id)->get();

        foreach ($primes as $prime) {
        $deplacements = $prime->deplacements;
            foreach ($deplacements as $deplacement) {
                $startDate = Carbon::parse($deplacement->date_debut);
                $endDate = Carbon::parse($deplacement->date_fin);

                // Push start date to startDates
                if (!empty($deplacement->date_debut)) {
                    $startDates->push($startDate->toDateString());
                }

                // Push all dates in the period to dates
                if ($startDate && $endDate && $startDate->lte($endDate)) {
                    $period = CarbonPeriod::create($startDate, $endDate);
                    foreach ($period as $date) {
                        $dates->push($date->toDateString());
                    }
                }
            }
        }

        $JFs = JoursFeries::all();
        foreach ($JFs as $jf) {
            $date = Carbon::parse($jf->date);
            $dates->push($date->toDateString());
        }

        
        // Remove duplicates and sort
        $disallowedDates = $dates->unique()->sort()->values();
        $year = Carbon::now()->year;

        // Step 1: Generate all days of the year
        $allDaysOfYear = collect();
        $period = CarbonPeriod::create("$year-01-01", "$year-12-31");
        foreach ($period as $date) {
            $allDaysOfYear->push($date->toDateString());
        }

        // Step 2: Remove the collected $dates (subtract them)
        $missingDays = $allDaysOfYear->diff($disallowedDates);

        // Step 3: Return the missing dates (sorted)
        $allowedDates = $missingDays->values();

         activity()
        ->causedBy(Auth::user())
        ->performedOn($personnel)
        ->withProperties([
            'id_personnel' => $personnel->id,
            'nom_complet' => $personnel->nom . ' ' . $personnel->prenom,
            'ancienne_valeur' => null,
            'nouvelle_valeur' => null,
        ])
        ->log('Consultation du profil du personnel');

        return Inertia::render('Personnel/Show',compact('personnel', 'moisPvtArray','lignes_budgetaires','trajets','allowedDates','startDates','Ikavailablemonths'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Personnel $personnel)
    {
        // Eager load the 'vehicule' relation
        $personnel->load('vehicule');

        // Pass the personnel object to the view
        return Inertia::render('Personnel/Edit', compact('personnel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PersonnelUpdateRequest $request, Personnel $personnel)
    {
        // Validation des données via PersonnelUpdateRequest
        $validatedData = $request->validated();
       $oldData = $personnel->getOriginal();

        // Mise à jour des attributs principaux
        $personnel->nom = $validatedData['nom'];
        $personnel->prenom = $validatedData['prenom'];
        $personnel->num_cin =  (string) $validatedData['num_cin'];
        $personnel->num_ppr = (string) $validatedData['num_ppr'];
        $personnel->grade = $validatedData['grade'];
        $personnel->echelle = $validatedData['echelle'];
        $personnel->groupe = $validatedData['groupe'];
        $personnel->taux_indemnite = $validatedData['taux_indemnite'];
        $personnel->montant_indemnite = $validatedData['montant_indemnite'];
        $personnel->banque_rib = (string) $validatedData['banque_rib'];
        $personnel->guichet_rib = (string) $validatedData['guichet_rib'];
        $personnel->num_compte_rib = (string) $validatedData['num_compte_rib'];
        $personnel->code_rib = (string) $validatedData['code_rib'];
        $personnel->residence = $validatedData['residence'];
        $personnel->creance = $validatedData['creance'];
        $personnel->suffix = $validatedData['suffix'];
        $personnel->situation_familiale = $validatedData['situation_familiale'];
        $personnel->save();

        // Gestion des informations sur le véhicule, si elles sont présentes
        if (isset($validatedData['vehicule']['nom'])) {
            $vehiculeData = $validatedData['vehicule'];

            // Vérifier si un véhicule est déjà associé
            $vehicule = $personnel->vehicule;
            if ($vehicule) {
                // Mise à jour du véhicule existant
                $vehicule->nom = $vehiculeData['nom'] ?? $vehicule->nom;
                $vehicule->matricule = $vehiculeData['matricule'] ?? $vehicule->matricule;
                $vehicule->puissance = $vehiculeData['puissance'] ?? $vehicule->puissance;
                $vehicule->limite_annuel = $vehiculeData['limite_annuel'] ?? $vehicule->limite_annuel;
                $vehicule->save();
            } else {
                // Création d'un nouveau véhicule si aucun n'est associé
                $vehicule = new Vehicule();
                $vehicule->nom = $vehiculeData['nom'];
                $vehicule->matricule = $vehiculeData['matricule'];
                $vehicule->puissance = $vehiculeData['puissance'];
                $vehicule->limite_annuel = $vehiculeData['limite_annuel'];

                $personnel->vehicule()->save($vehicule);
            }
        }
        $newData = $personnel->fresh()->toArray();

    $excludeFields = ['updated_at', 'created_at', 'fullname'];

// Filtrer les données avant comparaison pour exclure ces champs
$filteredOldData = array_diff_key($oldData, array_flip($excludeFields));
$filteredNewData = array_diff_key($newData, array_flip($excludeFields));

// Trouver uniquement les champs modifiés parmi les champs filtrés
$diff = array_diff_assoc($filteredNewData, $filteredOldData);

$oldChanged = array_intersect_key($filteredOldData, $diff);
$newChanged = array_intersect_key($filteredNewData, $diff);
    // Log d'activité
    activity()
    ->causedBy(Auth::user())
    ->performedOn($personnel)
    ->withProperties([
        'id_personnel' => $personnel->id,
        'nom_complet' => $personnel->nom . ' ' . $personnel->prenom,
        'ancienne_valeur' => $oldChanged,
        'nouvelle_valeur' => $newChanged,
    ])
    ->log('Modification des informations du personnel');
        // Redirection après mise à jour
        return Redirect::route('personnels.index')->with('success', 'Personnel mis à jour avec succès!');
    }
    
    public function toggle(Personnel $personnel)
    {   
        $ancienneValeur = $personnel->statut;
        $personnel->statut = $personnel->statut == 0 ? 1 : 0;

        // Sauvegarder les modifications et vérifier si elles ont réussi
        if ($personnel->save()) {
             

       activity()
    ->causedBy(Auth::user())
    ->performedOn($personnel)
    ->withProperties([
        'id_personnel' => $personnel->id,
        'nom_complet' => $personnel->nom . ' ' . $personnel->prenom,
        'ancienne_valeur' => ['statut' => $ancienneValeur], 
        'nouvelle_valeur' => ['statut' => $personnel->statut], 
    ])
    ->log('Changement du statut du personnel');

            return response()->json([
                'success' => true,
                'message' => 'Le statut du personnel a été mis à jour avec succès.',
                'new_status' => $personnel->statut
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Une erreur s\'est produite lors de la mise à jour du statut.'
        ], 500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Personnel $personnel)
    {
        //
    }
}
