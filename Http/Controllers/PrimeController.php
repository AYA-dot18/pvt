<?php

namespace App\Http\Controllers;

use App\Models\Prime;
use App\Models\Trajet;
use App\Models\Ik;
use App\Models\Personnel;
use App\Models\MoisPvt;
use App\Models\EtatSomme;
use App\Models\LigneBudgetaire;
use Illuminate\Http\Request;
use App\Http\Requests\PrimeStoreRequest;
use App\Http\Requests\PrimeUpdateRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use NumberFormatter;
use Illuminate\Support\Facades\Auth; 

class PrimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function fetch(Request $request, Personnel $personnel)
    {
        $total = Prime::where('personnel_id',$personnel->id)->count();
        $query = Prime::query()->with(['moisPvts',
        'deplacements' => function ($q) {
            $q->orderBy('etat_somme_id', 'asc');
        },
        'deplacements.trajet',
        'deplacements.ligneBudgetaire',
        'deplacements.etatSomme',
        'deplacements.ik']); // deplacements by date_debut asc
        $query->where('personnel_id',$personnel->id);
        if ($request->has('sortBy')) {
            $sort = $request->get('sortBy');
            $sortBy = $sort[0]['key'];
            $sortOrder= $sort[0]['order'];
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Default sorting by id if no specific sorting is requested
            $query->orderBy('id', 'DESC');
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
       
        return response()->json([
            'primes' => $items,
            'total' => $items->total(),
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(PrimeStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            $personnel = Personnel::findOrFail($validatedData['personnel_id']);

            // Création de la prime
            $prime = Prime::create([
                'personnel_id' => $validatedData['personnel_id'],
                'type' => $validatedData['type'],
                'creance_cree' => $validatedData['creance_cree'],
                'ancienne_creance' => $personnel->creance,
                'nouvelle_creance' => $personnel->creance + $validatedData['creance_cree'],
                'changetaux' => $validatedData['changetaux'],
                'montant_initial' => $validatedData['montant'] + $validatedData['creance_cree'],
                'montant' => $validatedData['montant'],
                'nombre_taux' => $validatedData['nombre_taux'] ?? null,
                'remarque' => $validatedData['remarque'] ?? null,
            ]);

            // Mise à jour de la créance du personnel
            if ($validatedData['creance_cree'] != 0) {
                $personnel->creance += round($validatedData['creance_cree']);
                $personnel->save();
            }

            // Si type = pvt, enregistrer les mois
            if ($validatedData['type'] == 'pvt') {
                foreach ($validatedData['mois'] as $mois) {
                    $prime->moisPvts()->create([
                        'mois' => $mois,
                        'annee' => now()->year,
                    ]);
                }
            }

            $groupedDeplacements = collect();
            $groupedIks = collect();

            if (!empty($validatedData['deplacements'])) {
                foreach ($validatedData['deplacements'] as $deplacement) {
                    // Déterminer les heures de départ et retour selon le nombre de repas
                    [$heure_depart, $heure_retour] = match ($deplacement['repas']) {
                        3 => ['16H', '16H'],
                        4 => ['09H', '16H'],
                        5 => ['09H', '22H'],
                        default => [null, null],
                    };

                    // Récupérer les informations du véhicule
                    $vehicule = $personnel->vehicule?->nom 
                        ? "Personnel ".$personnel->vehicule->nom." Matricule | ".$personnel->vehicule->matricule." |  ".$personnel->vehicule->puissance." CH"
                        : '';

                    // Récupérer les informations du trajet
                    $trajet = Trajet::findOrFail($deplacement['trajet_id']);

                    // Déplacement PDF
                    $data = [
                        'fullname' => "{$personnel->nom} {$personnel->prenom}",
                        'grade' => $personnel->grade,
                        'ville' => $trajet->ville,
                        'vehicule' => $vehicule,
                        'date_depart' => Carbon::parse($deplacement['date_debut'])->format('d/m/Y'),
                        'date_retour' => Carbon::parse($deplacement['date_fin'])->format('d/m/Y'),
                    ];

                    // Définir le dossier de stockage
                    $folderPath = "/".$data['fullname']."/".$prime->type."/missions/";
                    Storage::disk('public')->makeDirectory($folderPath);

                    // Générer et stocker le PDF
                    $fileName = "mission_" . Carbon::now()->format('YmdHis') . ".pdf";
                    $filePath = $folderPath . $fileName;
                    $pdf = Pdf::loadView('pdf.mission', $data);
                    Storage::disk('public')->put($filePath, $pdf->output());
                    $mois = Carbon::parse($deplacement['date_debut'])->format('m');
                    // Enregistrement du déplacement
                    $deplacementSaved = $prime->deplacements()->create([
                        'trajet_id' => $deplacement['trajet_id'],
                        'ligne_budgetaire_id' => $deplacement['ligne_budgetaire_id'],
                        'montant' => $deplacement['montant'],
                        'ordre_mission_path' => $filePath,
                        'nombre_taux' => $deplacement['nombre_taux'] ?? null,
                        'mois' => $mois,
                        'date_debut' => $deplacement['date_debut'],
                        'date_fin' => $deplacement['date_fin'],
                        'repas' => $deplacement['repas'],
                        'heure_depart' => $heure_depart,
                        'heure_retour' => $heure_retour,
                    ]);
                    $deplacementSaved->load('trajet');

                    if($deplacement['has_ik'] == true){
                        $ikSaved = Ik::create([
                            'deplacement_id' => $deplacementSaved->id,
                            'prime_id' => $prime->id,
                            'mois' => $mois,
                            'montant' => $deplacement['ik']['montant'],
                            'ligne_budgetaire_id' => $deplacement['ik']['ligne_budgetaire_id']
                        ]);
                        $deplacementSaved->update(['ik_id' => $ikSaved->id]);
                        $groupedIks->push($ikSaved);
                    } 

                    // Groupement des déplacements par ligne budgétaire et mois
                    $groupKey = "{$deplacementSaved->ligne_budgetaire_id}_{$deplacementSaved->mois}";
                    $groupedDeplacements->push($deplacementSaved);
                }
                // Génération de l'état somme pour chaque groupe
                foreach ($groupedDeplacements->groupBy(fn ($d) => "{$d->ligne_budgetaire_id}_{$d->mois}") as $group) {
                    $moisDeplacement = Carbon::parse($group->first()->date_debut)->format('m');
                    $moisDeplacement_lettre = ucfirst(Carbon::parse($group->first()->date_debut)->translatedFormat('F'));
                    // Récupérer la ligne budgétaire associée au premier déplacement du groupe
                    $ligneBudgetaire = LigneBudgetaire::find($group->first()->ligne_budgetaire_id);

                    if (!$ligneBudgetaire) {
                        throw new \Exception("Ligne budgétaire introuvable pour l'ID : {$group->first()->ligne_budgetaire_id}");
                    }
                    $montant = $group->sum('montant');
                    $parts = explode('.', $montant);
                    // Initialiser les variables
                    $partie_entiere = $parts[0];
                    $partie_decimale = isset($parts[1]) ? str_pad($parts[1], 2, '0', STR_PAD_RIGHT) : null;
                    $entiere_text = ucwords($this->nombreEnLettres($partie_entiere));
                    $decimale_text = $partie_decimale ? 'et '. ucwords($this->nombreEnLettres($partie_decimale)) . ' Centimes' : '';
                    $total_text = $entiere_text.' Dirhams'. $decimale_text;
                    $etatSommeData = [
                        'exercice' => $ligneBudgetaire->exercice,
                        'chapitre' => $ligneBudgetaire->chapitre,
                        'article' => $ligneBudgetaire->article,
                        'paragraphe' => $ligneBudgetaire->paragraphe,
                        'ligne' => $ligneBudgetaire->ligne,
                        'fullname' => $personnel->nom." ". $personnel->prenom,
                        'grade' => $personnel->grade,
                        'echelle' => $personnel->echelle,
                        'cin' => $personnel->num_cin,
                        'ddr' => $personnel->num_ppr,
                        'mois' => $moisDeplacement_lettre,
                        'deplacements' => $group,
                        'rib' => $personnel->banque_rib.' '.$personnel->guichet_rib.' '.$personnel->num_compte_rib.' '.$personnel->code_rib,
                        'taux_indemnite' => $personnel->taux_indemnite,
                        'montant' => number_format(round($group->sum('montant'), 2), 2, ',', ' '),
                        'montant_text' => $total_text
                    ];

                    // Génération et stockage du PDF
                    $folderPath = "/".$etatSommeData['fullname']."/".$prime->type."/etatsomme_prime/".$moisDeplacement."/";
                    Storage::disk('public')->makeDirectory($folderPath);

                    $fileName = "etat_somme_".$moisDeplacement."_" . Carbon::now()->format('YmdHis') . ".pdf";
                    $filePath = $folderPath . $fileName;
                    $pdf = Pdf::loadView('pdf.etatsomme_prime', $etatSommeData);
                    Storage::disk('public')->put($filePath, $pdf->output());

                    // Enregistrer l'état somme
                    $etatSomme = EtatSomme::create([
                        'montant' => $group->sum('montant'),
                        'type' => 'prime',
                        'vehicule_nom' => $personnel->vehicule?->nom ?? null,
                        'vehicule_matricule' => $personnel->vehicule?->matricule ?? null,
                        'vehicule_puissance' => $personnel->vehicule?->puissance ?? null,
                        'vehicule_limite_annuel' => $personnel->vehicule?->limite_annuel ?? null,
                        'etat_somme_path' => $filePath,
                        'mois' => $moisDeplacement,
                    ]);
                    // Lier les déplacements à cet état somme
                    $group->each(fn ($d) => $d->update(['etat_somme_id' => $etatSomme->id]));
                }
                // Génération de l'état somme pour chaque groupe
                foreach ($groupedIks->groupBy(fn ($i) => "{$i->ligne_budgetaire_id}_{$i->mois}") as $group) {

                }

            }

            DB::commit();
           activity()
    ->causedBy(Auth::user())
    ->performedOn($prime)
    ->withProperties([
        'id_personnel' => $prime->personnel_id,
        'nom_complet' => $prime->personnel->nom . ' ' . $prime->personnel->prenom,
        'ancienne_valeur' => null,
        'nouvelle_valeur' => [
            'type' => $prime->type,
            'montant' => $prime->montant,
            'creance_cree' => $prime->creance_cree,
            'nouvelle_creance' => $prime->nouvelle_creance,
        ],
    ])
    ->log('Ajout d\'une prime');

            return redirect()->back()->with('success', 'Prime créée avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    private function nombreEnLettres($nombre)
    {
        $formatter = new NumberFormatter('fr_FR', NumberFormatter::SPELLOUT);
    
        return $formatter->format($nombre);
    }
    /**
     * Display the specified resource.
     */
    public function show(Prime $prime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prime $prime)
    {
        //
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(PrimeUpdateRequest $request, Prime $prime)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            $personnel = Personnel::findOrFail($validatedData['personnel_id']);

            $anciennesvaleurs = $prime->only([
            'type', 'creance_cree', 'changetaux', 'montant_initial', 'montant', 'nombre_taux', 'remarque'
        ]);

            // Restaurer la créance de l'ancien montant avant mise à jour
            if ($prime->creance_cree != 0) {
                $personnel->creance -= round($prime->creance_cree);
            }

            // Mise à jour des mois pour les primes de type 'pvt'
            $prime->moisPvts()->delete();
            if ($validatedData['type'] == 'pvt') {
                foreach ($validatedData['mois'] as $mois) {
                    $prime->moisPvts()->create([
                        'mois' => $mois,
                        'annee' => now()->year,
                    ]);
                }
            }

            // Mettre à jour les informations de la prime
            $prime->update([
                'type' => $validatedData['type'],
                'creance_cree' => $validatedData['creance_cree'],
                'ancienne_creance' => $personnel->creance,
                'nouvelle_creance' => $personnel->creance + $validatedData['creance_cree'],
                'changetaux' => $validatedData['changetaux'],
                'montant_initial' => $validatedData['montant'] + $validatedData['creance_cree'],
                'montant' => $validatedData['montant'],
                'nombre_taux' => $validatedData['nombre_taux'] ?? null,
                'remarque' => $validatedData['remarque'] ?? null,
            ]);
            $nouvellesvaleurs = $prime->only([
            'type', 'creance_cree', 'changetaux', 'montant_initial', 'montant', 'nombre_taux', 'remarque'
        ]);

            // Mettre à jour la créance du personnel
            if ($validatedData['creance_cree'] != 0) {
                $personnel->creance += round($validatedData['creance_cree']);
                $personnel->save();
            }

            // Suppression des anciens déplacements avant mise à jour
            $prime->deplacements()->delete();

            $groupedDeplacements = collect();
            $groupedIks = collect();

            if (!empty($validatedData['deplacements'])) {
                foreach ($validatedData['deplacements'] as $deplacement) {
                    [$heure_depart, $heure_retour] = match ($deplacement['repas']) {
                        3 => ['16H', '16H'],
                        4 => ['09H', '16H'],
                        5 => ['09H', '22H'],
                        default => [null, null],
                    };
    
                    $vehicule = $personnel->vehicule?->nom 
                        ? "Personnel ".$personnel->vehicule->nom." Matricule | ".$personnel->vehicule->matricule." |  ".$personnel->vehicule->puissance." CH"
                        : '';
    
                    $trajet = Trajet::findOrFail($deplacement['trajet_id']);
    
                    $data = [
                        'fullname' => "{$personnel->nom} {$personnel->prenom}",
                        'grade' => $personnel->grade,
                        'ville' => $trajet->ville,
                        'vehicule' => $vehicule,
                        'date_depart' => Carbon::parse($deplacement['date_debut'])->format('d/m/Y'),
                        'date_retour' => Carbon::parse($deplacement['date_fin'])->format('d/m/Y'),
                    ];
    
                    $folderPath = "/".$data['fullname']."/".$prime->type."/missions/";
                    Storage::disk('public')->makeDirectory($folderPath);
    
                    $fileName = "mission_" . Carbon::now()->format('YmdHis') . ".pdf";
                    $filePath = $folderPath . $fileName;
                    $pdf = Pdf::loadView('pdf.mission', $data);
                    Storage::disk('public')->put($filePath, $pdf->output());
                    $mois = Carbon::parse($deplacement['date_debut'])->format('m');
                    $deplacementSaved = $prime->deplacements()->create([
                        'trajet_id' => $deplacement['trajet_id'],
                        'ligne_budgetaire_id' => $deplacement['ligne_budgetaire_id'],
                        'montant' => $deplacement['montant'],
                        'ordre_mission_path' => $filePath,
                        'nombre_taux' => $deplacement['nombre_taux'] ?? null,
                        'mois' => $mois,
                        'date_debut' => $deplacement['date_debut'],
                        'date_fin' => $deplacement['date_fin'],
                        'repas' => $deplacement['repas'],
                        'heure_depart' => $heure_depart,
                        'heure_retour' => $heure_retour,
                    ]);
                    Ik::where('deplacement_id',$deplacementSaved->id)->delete();
                    if($deplacement['has_ik'] == true){
                        $ikSaved = Ik::create([
                            'deplacement_id' => $deplacementSaved->id,
                            'prime_id' => $prime->id,
                            'mois' => $mois,
                            'montant' => $deplacement['ik']['montant'],
                            'ligne_budgetaire_id' => $deplacement['ik']['ligne_budgetaire_id']
                        ]);
                        $deplacementSaved->update(['ik_id' => $ikSaved->id]);
                    } 
                    $groupedDeplacements->push($deplacementSaved);
                }
                /// Génération des états de somme
                foreach ($groupedDeplacements->groupBy(fn ($d) => "{$d->ligne_budgetaire_id}_{$d->mois}") as $group) {
                    $moisDeplacement = Carbon::parse($group->first()->date_debut)->format('m');
                    $moisDeplacement_lettre = ucfirst(Carbon::parse($group->first()->date_debut)->translatedFormat('F'));
                    $ligneBudgetaire = LigneBudgetaire::find($group->first()->ligne_budgetaire_id);

                    if (!$ligneBudgetaire) {
                        throw new \Exception("Ligne budgétaire introuvable pour l'ID : {$group->first()->ligne_budgetaire_id}");
                    }
                    $montant = $group->sum('montant');
                    $parts = explode('.', $montant);
                    // Initialiser les variables
                    $partie_entiere = $parts[0];
                    $partie_decimale = isset($parts[1]) ? str_pad($parts[1], 2, '0', STR_PAD_RIGHT) : null;
                    $entiere_text = ucwords($this->nombreEnLettres($partie_entiere));
                    $decimale_text = $partie_decimale ? 'et '. ucwords($this->nombreEnLettres($partie_decimale)) . ' Centimes' : '';
                    $total_text = $entiere_text.' Dirhams'. $decimale_text;
                    $etatSommeData = [
                        'exercice' => $ligneBudgetaire->exercice,
                        'chapitre' => $ligneBudgetaire->chapitre,
                        'article' => $ligneBudgetaire->article,
                        'paragraphe' => $ligneBudgetaire->paragraphe,
                        'ligne' => $ligneBudgetaire->ligne,
                        'fullname' => $personnel->nom." ". $personnel->prenom,
                        'grade' => $personnel->grade,
                        'echelle' => $personnel->echelle,
                        'cin' => $personnel->num_cin,
                        'ddr' => $personnel->num_ppr,
                        'mois' => $moisDeplacement_lettre,
                        'deplacements' => $group,
                        'rib' => $personnel->banque_rib.' '.$personnel->guichet_rib.' '.$personnel->num_compte_rib.' '.$personnel->code_rib,
                        'taux_indemnite' => $personnel->taux_indemnite,
                        'montant' => number_format(round($group->sum('montant'), 2), 2, ',', ' '),
                        'montant_text' => $total_text
                    ];

                    
                    // Génération et stockage du PDF
                    $folderPath = "/".$etatSommeData['fullname']."/".$prime->type."/etatsomme_prime/".$moisDeplacement."/";
                    Storage::disk('public')->makeDirectory($folderPath);

                    $fileName = "etat_somme_".$moisDeplacement."_" . Carbon::now()->format('YmdHis') . ".pdf";
                    $filePath = $folderPath . $fileName;
                    $pdf = Pdf::loadView('pdf.etatsomme_prime', $etatSommeData);
                    Storage::disk('public')->put($filePath, $pdf->output());

                    // Enregistrer l'état somme
                    $etatSomme = EtatSomme::create([
                        'montant' => $group->sum('montant'),
                        'type' => 'prime',
                        'vehicule_nom' => $personnel->vehicule?->nom ?? null,
                        'vehicule_matricule' => $personnel->vehicule?->matricule ?? null,
                        'vehicule_puissance' => $personnel->vehicule?->puissance ?? null,
                        'vehicule_limite_annuel' => $personnel->vehicule?->limite_annuel ?? null,
                        'etat_somme_path' => $filePath,
                        'mois' => $moisDeplacement,
                    ]);

                    // Lier les déplacements à cet état somme
                    $group->each(fn ($d) => $d->update(['etat_somme_id' => $etatSomme->id]));
                }
            }
            

            // Trouver uniquement les champs modifiés entre ancien et nouveau
$diff = array_diff_assoc($nouvellesvaleurs, $anciennesvaleurs);
$oldChanged = array_intersect_key($anciennesvaleurs, $diff);
$newChanged = array_intersect_key($nouvellesvaleurs, $diff);

activity()
    ->causedBy(Auth::user())
    ->performedOn($personnel)
    ->withProperties([
        'id_personnel' => $personnel->id,
        'ancienne_valeur' => $oldChanged,
        'nouvelle_valeur' => $newChanged,
    ])
    ->log('Modification des informations de la prime');



            DB::commit();
            return redirect()->back()->with('success', 'Prime mise à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prime $prime)
    {
        //
    }
}
