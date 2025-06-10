<?php

namespace App\Http\Controllers;

use App\Models\Tgr;
use App\Models\LigneBudgetaire;
use App\Models\Deplacement;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Exports\TgrExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth; 
use Spatie\Activitylog\Models\Activity;

class TgrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lignes_budgetaires = LigneBudgetaire::all();
        activity()
        ->causedBy(Auth::user())
        ->withProperties([
            'id_personnel'     => null,
            'ancienne_valeur'  => null,
            'nouvelle_valeur'  => null,
        ])
        ->log('Consultation de la liste des déplacements');
        return Inertia::render('Tgr/Index',compact('lignes_budgetaires'));
    }
    
    public function fetchdeplacements(Request $request)
    {
        $deplacements = Deplacement::select('deplacements.*')
        ->join('primes', 'deplacements.prime_id', '=', 'primes.id')
        ->join('personnels', 'primes.personnel_id', '=', 'personnels.id')
        ->whereNull('deplacements.tgr_id')
        ->where('deplacements.ligne_budgetaire_id', $request->ligne_budgetaire_id)
        ->orderBy('personnels.id', 'ASC')
        ->with('trajet', 'prime.personnel')
        ->get();

        
        return response()->json([
            'deplacements' => $deplacements,
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $deplacements = $request->input('deplacements');
        $type = $request->input('type');
        $ligne_budgetaire_id = $request->input('ligne_budgetaire_id');
        $montant = $request->input('montant');
        
        // Récupérer la ligne budgétaire
        $lignebudgetaire = LigneBudgetaire::findOrFail($ligne_budgetaire_id);
        $ligne_budgetaire = $lignebudgetaire->ligne_budgetaire;

        unset($dep); // Éviter toute référence persistante non désirée

        // Créer un TGR
        $tgr = Tgr::create([
            'montant' => $montant,
            'type' => 'Prime',
            'tgr_path' => '',
            'statut' => 'en_attente',
            'ligne_budgetaire_id' => $ligne_budgetaire_id,
        ]);

        // Générer et stocker le fichier Excel
        $fileName = 'tgr_' . $tgr->id . '.xlsx';
        $filePath = 'exports/' . $fileName;

        Excel::store(new TgrExport($deplacements, $type, $ligne_budgetaire, $montant), $filePath, 'local');
        
        // Mettre à jour le TGR avec le chemin du fichier
        $tgr->update(['tgr_path' => $filePath]);

        // Associer chaque déplacement à ce TGR
        foreach ($deplacements as $dep) {
            $deplacementModel = Deplacement::find($dep['id']);
        $personnel = $deplacementModel->prime->personnel;

        $anciennesvaleurs = ['tgr_id' => $deplacementModel->tgr_id];
        $nouvellesvaleurs = ['tgr_id' => $tgr->id];
        
            Deplacement::where('id', $dep['id'])->update(['tgr_id' => $tgr->id]);
            activity()
            ->causedBy(Auth::user())
            ->performedOn($personnel)
            ->withProperties([
                'id_personnel' => $personnel->id,
            ])
            ->log('Association d\'un déplacement au TGR #' . $tgr->id);
        }

        session()->flash('tgr_id', $tgr->id);
        session()->flash('success', 'Fichier TGR créé avec succès !');

        return back();
    }

    public function download(Request $request)
    {
        $tgr_id = $request->query('tgr_id');

        // Récupérer le TGR et vérifier si le fichier existe
        $tgr = Tgr::findOrFail($tgr_id);

        if (!Storage::exists($tgr->tgr_path)) {
            return response()->json(['error' => 'Fichier introuvable'], 404);
        }

         activity()
        ->causedBy(Auth::user())
        ->withProperties([
            'id_personnel' => null,
            'ancienne_valeur' => null,
            'nouvelle_valeur' => null,
        ])
        ->log('Téléchargement du fichier TGR #' . $tgr->id);
        return response()->download(storage_path("app/" . $tgr->tgr_path));
    }

    public function historique()
    {
        $tgrs = Tgr::with('deplacements','ligneBudgetaire')
        ->orderBy('id','DESC')
        ->get();

        return Inertia::render('Tgr/Historique',compact('tgrs'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Tgr $tgr)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tgr $tgr)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tgr $tgr)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tgr $tgr)
    {
        $oldData = [
        'montant' => $tgr->montant,
        'type' => $tgr->type,
        'statut' => $tgr->statut,
        'ligne_budgetaire_id' => $tgr->ligne_budgetaire_id,
        'fichier' => $tgr->tgr_path,
    ];
        if ($tgr->tgr_path && Storage::disk('local')->exists($tgr->tgr_path)) {
            Storage::disk('local')->delete($tgr->tgr_path);
        }
        
        // Détacher les déplacements du TGR
        $tgr->deplacements()->update(['tgr_id' => null]);

      activity()
        ->causedBy(Auth::user())
        ->performedOn($tgr)
        ->withProperties([
            'ancienne_valeur' => $oldData,
            'nouvelle_valeur' => null,
        ])
        ->log('Suppression d\'un fichier TGR');;

        return redirect()->back()->with('success','Fichier Tgr supprimé avec succès!');
    }
}
