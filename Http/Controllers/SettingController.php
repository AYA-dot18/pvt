<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use App\Models\JoursFeries;
use App\Models\LigneBudgetaire;
use App\Http\Requests\JFStoreRequest;
use App\Http\Requests\JFUpdateRequest;
use App\Http\Requests\LBStoreRequest;
use App\Http\Requests\LBUpdateRequest;
use Illuminate\Support\Facades\Auth; 

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Personnel::all();
        activity()
        ->causedBy(Auth::user())
        ->withProperties([
            'id_personnel' => null,
            'ancienne_valeur' => null,
            'nouvelle_valeur' => null,
        ])
        ->log('Consultation de la page des paramètres');
        return Inertia::render('Settings/Index',compact('data'));
    }

    public function joursferiesfetch(Request $request)
    {
        $total = JoursFeries::count();
        $query = JoursFeries::query();
        if ($request->has('sortBy')) {
            $sort = $request->get('sortBy');
            $sortBy = $sort[0]['key'];
            $sortOrder= $sort[0]['order'];
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Default sorting by id if no specific sorting is requested
            $query->orderBy('date', 'DESC');
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
            'JF' => $items,
            'total' => $items->total(),
        ]);
    }
    public function lignesbudgetairesfetch(Request $request)
    {
        $total = LigneBudgetaire::count();
        $query = LigneBudgetaire::query();
        if ($request->has('sortBy')) {
            $sort = $request->get('sortBy');
            $sortBy = $sort[0]['key'];
            $sortOrder= $sort[0]['order'];
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Default sorting by id if no specific sorting is requested
            $query->orderBy('date', 'DESC');
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
            'LB' => $items,
            'total' => $items->total(),
        ]);
    }

    public function joursferiesstore(JFStoreRequest $request)
    {
        $validatedData = $request->validated();

        // Création d'une nouvelle instance de Personnel
        $JF = new JoursFeries();

        $JF->titre = $validatedData['titre'];
        $JF->date = Carbon::parse($validatedData['date'])->format('Y-m-d');

        $JF->save();

         activity()
    ->causedBy(Auth::user())
    ->performedOn($JF)
    ->withProperties([
        'ancienne_valeur' => null,
        'nouvelle_valeur' => [
            'titre' => $JF->titre,
            'date' => Carbon::parse($JF->date)->format('d/m/Y'),
        ],
    ])
    ->log('Ajout d’un jour férié');

        return Redirect::route('settings.index')->with('success','Jour férié ajouté avec succès!');
    }

    public function joursferiesupdate(JFUpdateRequest $request, JoursFeries $JF)
    {
        $validatedData = $request->validated();

        $oldData = [
        'titre' => $JF->titre,
        'date' => $JF->date,
    ];

        $JF->titre = $validatedData['titre'];
        $JF->date = Carbon::parse($validatedData['date'])->format('Y-m-d');

        $JF->save();
     $newData = [
        'titre' => $JF->titre,
        'date' => $JF->date,
    ];

    // Différence entre ancien et nouveau
    $diff = array_diff_assoc($newData, $oldData);

    activity()
        ->causedBy(Auth::user())
        ->performedOn($JF)
        ->withProperties([
            'ancienne_valeur' => array_intersect_key($oldData, $diff),
            'nouvelle_valeur' => array_intersect_key($newData, $diff),
        ])
        ->log('Modification d’un jour férié');
        return Redirect::route('settings.index')->with('success','Jour férié mis à jour avec succès!');
    }


    public function lignesbudgetairesstore(LBStoreRequest $request)
    {
        $validatedData = $request->validated();

        // Création d'une nouvelle instance de LignesBudgetaires
        $LB = new LigneBudgetaire();

        $LB->nom = $validatedData['nom'];
        $LB->type = $validatedData['type'];
        $LB->exercice = $validatedData['exercice'];
        $LB->chapitre = $validatedData['chapitre'];
        $LB->article = $validatedData['article'] ?? null;
        $LB->paragraphe = $validatedData['paragraphe'] ?? null;
        $LB->ligne = $validatedData['ligne'] ?? null;
        $LB->programme = $validatedData['programme'] ?? null;
        $LB->region = $validatedData['region'] ?? null;
        $LB->montant_initial = $validatedData['montant_initial'] ?? 0;
        $LB->montant_restant = $validatedData['montant_restant'] ?? 0;

        $LB->save();

       activity()
    ->causedBy(Auth::user())
    ->performedOn($LB)
    ->withProperties([
        'ancienne_valeur' => null,
    'nouvelle_valeur' => [
        'nom' => $LB->nom,
        'type' => $LB->type,
        'exercice' => $LB->exercice,
        'chapitre' => $LB->chapitre,
        'article' => $LB->article,
        'paragraphe' => $LB->paragraphe,
        'ligne' => $LB->ligne,
        'montant_initial' => $LB->montant_initial,
        'montant_restant'=> $LB->montant_restant,]
    ])
    ->log('Ajout d’une ligne budgétaire');

        return Redirect::route('settings.index')->with('success', 'Ligne budgétaire ajoutée avec succès!');
    }

    public function lignesbudgetairesupdate(LBUpdateRequest $request,LigneBudgetaire $LB)
    {
        $validatedData = $request->validated();

        $anciennesValeurs = $LB->toArray();

        $LB->nom = $validatedData['nom'];
        $LB->type = $validatedData['type'];
        $LB->exercice = $validatedData['exercice'];
        $LB->chapitre = $validatedData['chapitre'];
        $LB->article = $validatedData['article'] ?? null;
        $LB->paragraphe = $validatedData['paragraphe'] ?? null;
        $LB->ligne = $validatedData['ligne'] ?? null;
        $LB->programme = $validatedData['programme'] ?? null;
        $LB->region = $validatedData['region'] ?? null;
        $LB->projet = $validatedData['projet'] ?? null;
        $LB->montant_initial = $validatedData['montant_initial'] ?? 0;

        $LB->save();

         $nouvellesValeurs = $LB->toArray();

    // Calcul des différences entre anciennes et nouvelles valeurs
   $diff = [];
    foreach ($nouvellesValeurs as $key => $value) {
        if ($key === 'updated_at') {
            continue;
        }
        if (array_key_exists($key, $anciennesValeurs) && $anciennesValeurs[$key] != $value) {
            $diff[$key] = true;
        }
    }

    activity()
        ->causedBy(Auth::user())
        ->performedOn($LB)
        ->withProperties([
            'ancienne_valeur' => array_intersect_key($anciennesValeurs, $diff),
            'nouvelle_valeur' => array_intersect_key($nouvellesValeurs, $diff),
        ])
        ->log('Modification d’une ligne budgétaire');

        return Redirect::route('settings.index')->with('success', 'Ligne budgétaire mis à jour avec succès!');
    }

    public function joursferiesdestroy(JoursFeries $JoursFeries)
    {
        $ancienneValeur = $JoursFeries->toArray();
        if ($JoursFeries->delete()) {
             activity()
            ->causedBy(Auth::user())
            ->performedOn($JoursFeries)
            ->withProperties([
                'ancienne_valeur' => $ancienneValeur,
                'nouvelle_valeur' => null,
            ])
            ->log('Suppression d’un jour férié');

            return redirect()->back()->with('success', 'Jour férié a été supprimé avec succès!');
        }
        return redirect()->back()->withErrors('error', 'Une erreur s\'est produite lors de la suppression du jour férié!');
    }

    public function lignesbudgetairesdestroy(LigneBudgetaire $lignebudgetaire)
    {
        $ancienneValeur = $lignebudgetaire->toArray();
        if ($lignebudgetaire->delete()) {
            activity()
            ->causedBy(Auth::user())
            ->performedOn($lignebudgetaire)
            ->withProperties([
                'ancienne_valeur' => $ancienneValeur,
                'nouvelle_valeur' => null,
            ])
            ->log('Suppression d’une ligne budgétaire');
            return redirect()->back()->with('success', 'Ligne budgétaire a été supprimé avec succès!');
        }
        return redirect()->back()->withErrors('error', 'Une erreur s\'est produite lors de la suppression de la ligne budgétaire!');
    }
}
