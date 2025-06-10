<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\Personnel;
use Illuminate\Http\Request;
use App\Http\Requests\CongeStoreRequest;
use App\Http\Requests\CongeUpdateRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth; 

class CongeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       
    }

    public function fetch(Request $request, Personnel $personnel)
    {
        $total = Conge::where('personnel_id',$personnel->id)->count();
        $query = Conge::query();
        $query->where('personnel_id',$personnel->id);
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
       
        return response()->json([
            'conges' => $items,
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
    public function store(CongeStoreRequest $request)
    {
        $validatedData = $request->validated();
        $conge = new Conge();

        $conge->personnel_id = $validatedData['personnel_id'];
        $conge->date_debut = $validatedData['date_debut'];
        $conge->date_fin = $validatedData['date_fin'];
        $conge->type = $validatedData['type'];
        $conge->remarque = $validatedData['remarque'];
        
        if ($conge->save()) {
            $personnel = \App\Models\Personnel::find($conge->personnel_id);

        activity()
    ->causedBy(Auth::user())
    ->performedOn($conge)
    ->withProperties([
        'id_personnel' => $conge->personnel_id,
        'nom_complet' => $conge->personnel->nom . ' ' . $conge->personnel->prenom,
        'ancienne_valeur' => null,
        'nouvelle_valeur' => [
            'type' => $conge->type,
            'date_debut' => $conge->date_debut,
            'date_fin' => $conge->date_fin,
        ],
    ])
    ->log('Ajout d\'un congé');

            return redirect()->back()->with('success', 'Le congé a été ajouté avec succès!');
        }

        return redirect()->back()->withErrors(['error' => 'Échec de l\'ajout du congé.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Conge $conge)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Conge $conge)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CongeUpdateRequest $request, Conge $conge)
    {
        $validatedData = $request->validated();
$oldData = [
        'date_debut' => optional($conge->date_debut)->format('Y-m-d'),
        'date_fin' => optional($conge->date_fin)->format('Y-m-d'),
        'type' => $conge->type,
        'remarque' => $conge->remarque,
    ];

        $conge->date_debut = $validatedData['date_debut'];
        $conge->date_fin = $validatedData['date_fin'];
        $conge->type = $validatedData['type'];
        $conge->remarque = $validatedData['remarque'];
         $newData = [
        'date_debut' => optional($conge->date_debut)->format('Y-m-d'),
        'date_fin' => optional($conge->date_fin)->format('Y-m-d'),
        'type' => $conge->type,
        'remarque' => $conge->remarque,
    ];


  $diff = array_diff_assoc($newData, $oldData);
        if ($conge->save()) {
          if (!empty($diff)) {
            activity()
                ->causedBy(Auth::user())
                ->performedOn($conge)
                ->withProperties([
                    'ancienne_valeur' => array_intersect_key($oldData, $diff),
                    'nouvelle_valeur' => array_intersect_key($newData, $diff),
                    'nom_complet' => $conge->personnel->nom . ' ' . $conge->personnel->prenom,
                ])
                ->log('Mise à jour d\'un congé');
        }

            
            return redirect()->back()->with('success', 'Le congé a été mis à jour avec succès!');
        }
        return redirect()->back()->withErrors('error', 'Une erreur s\'est produite lors de la mise à jour du congé!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conge $conge)
    {
        $deletedData = [
        'date_debut' => $conge->date_debut,
        'date_fin' => $conge->date_fin,
        'type' => $conge->type,
    ];

    if ($conge->delete()) {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($conge)
            ->withProperties([
                'id_personnel' => $conge->personnel_id,
                'nom_complet' => $conge->personnel->nom . ' ' . $conge->personnel->prenom,
                'ancienne_valeur' => $deletedData,
                'nouvelle_valeur' => null,
            ])
            ->log('Suppression d\'un congé');
            return redirect()->back()->with('success', 'Le congé a été supprimé avec succès!');
        }
        return redirect()->back()->withErrors('error', 'Une erreur s\'est produite lors de la suppression du congé!');
    }
}
