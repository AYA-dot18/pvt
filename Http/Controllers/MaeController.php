<?php

namespace App\Http\Controllers;

use App\Models\Mae;
use App\Models\Personnel;
use App\Http\Requests\MaeStoreRequest;
use App\Http\Requests\MaeUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class MaeController extends Controller
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
        $total = Mae::where('personnel_id',$personnel->id)->count();
        $query = Mae::query();
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
            'maes' => $items,
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
    public function store(MaeStoreRequest $request)
    {
        $validatedData = $request->validated();
        $mae = new Mae();

        $mae->personnel_id = $validatedData['personnel_id'];
        $mae->date_depart = $validatedData['date_depart'];
        $mae->date_retour = $validatedData['date_retour'];
        $mae->destination = $validatedData['destination'];

        if ($mae->save()) {
            $personnel = \App\Models\Personnel::find($mae->personnel_id);

       
             activity()
            ->causedBy(Auth::user())
            ->performedOn($mae)
            ->withProperties([
                'id_personnel' => $mae->personnel_id,
                'nom_complet' => $personnel ? $personnel->nom . ' ' . $personnel->prenom : '',
                'ancienne_valeur' => null,
                'nouvelle_valeur' => [
                    'date_depart' => $mae->date_depart,
                    'date_retour' => $mae->date_retour,
                    'destination' => $mae->destination,
                ],
            ])
            ->log('Ajout d\'une mission à l\'étranger');
            return redirect()->back()->with('success', 'La mission à l\'étranger a été ajouté avec succès!');
        }

        return redirect()->back()->withErrors(['error' => 'Échec de l\'ajout de la mission à l\'étranger!']);
    }
    /**
     * Display the specified resource.
     */
    public function show(Mae $mae)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mae $mae)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MaeUpdateRequest $request, Mae $mae)
    {
        $validatedData = $request->validated();

        $oldData = [
        'date_depart' => $mae->date_depart,
        'date_retour' => $mae->date_retour,
        'destination' => $mae->destination,
    ];


        $mae->date_depart = $validatedData['date_depart'];
        $mae->date_retour = $validatedData['date_retour'];
        $mae->destination = $validatedData['destination'];

        $newData = [
        'date_depart' => $mae->date_depart,
        'date_retour' => $mae->date_retour,
        'destination' => $mae->destination,
    ];

   $diff = [];
    foreach ($newData as $key => $newVal) {
        $oldVal = $oldData[$key];

        if ($oldVal instanceof \Carbon\Carbon) {
            $oldVal = $oldVal->format('Y-m-d');
        }
        if ($newVal instanceof \Carbon\Carbon) {
            $newVal = $newVal->format('Y-m-d');
        }

        if ($oldVal != $newVal) {
            $diff[$key] = $newVal;
        }
    }


        if ($mae->save()&& !empty($diff)) {
            $personnel = $mae->personnel ?? \App\Models\Personnel::find($mae->personnel_id);
        activity()
            ->causedBy(Auth::user())
            ->performedOn($mae)
            ->withProperties([
                'ancienne_valeur' => array_intersect_key($oldData, $diff),
                'nouvelle_valeur' => array_intersect_key($newData, $diff),
                'nom_complet' => $personnel ? $personnel->nom . ' ' . $personnel->prenom : '',
                'id_personnel' => $mae->personnel_id,
            ])
            ->log('Mise à jour d\'une mission à l\'étranger');
            return redirect()->back()->with('success', 'La mission à l\'étranger a été mis à jour avec succès!');
        }
        return redirect()->back()->withErrors('error', 'Une erreur s\'est produite lors de la mise à jour de la mission à l\'étranger!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mae $mae)
    {

         $personnel = $mae->personnel ?? \App\Models\Personnel::find($mae->personnel_id);
 $deletedData = [
        'date_depart' => $mae->date_depart,
        'date_retour' => $mae->date_retour,
         'destination' => $mae->destination,
    ];

    if ($mae->delete()) {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($mae)
            ->withProperties([
                'id_personnel' => $mae->personnel_id,
                'nom_complet' => $personnel ? $personnel->nom . ' ' . $personnel->prenom : '',
                'ancienne_valeur' => $deletedData,
                'nouvelle_valeur' => null,
            ])
            ->log('Suppression d\'une mission à l\'étranger');
            return redirect()->back()->with('success', 'La mission à l\'étranger a été supprimé avec succès!');
        }
        return redirect()->back()->withErrors('error', 'Une erreur s\'est produite lors de la suppression de la mission à l\'étranger!');
    }
}
