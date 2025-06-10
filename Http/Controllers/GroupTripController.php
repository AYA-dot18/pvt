<?php
namespace App\Http\Controllers;

use App\Models\GroupTrip;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Deplacement;

class GroupTripController extends Controller
{
    // Lister toutes les missions
    public function index()
    {
        $groupTrips = GroupTrip::with('participants')->get();

        return inertia('groupTrip/index', [
            'groupTrips' => $groupTrips
        ]);
    }

    // Formulaire de création (optionnel si tu fais tout en Vue)
    public function create()
    {
        return inertia('groupTrip/Create');
    }

    // Enregistrer une mission
 

public function store(Request $request)
{
    $validated = $request->validate([
        'titre' => 'required|string',
        'lieu' => 'required|string',
        'date_depart' => 'required|date',
        'date_retour' => 'required|date|after_or_equal:date_depart',
        'type_mission' => 'nullable|string',
        'moyen_transport' => 'nullable|string',
        'ligne_budgetaire_id' => 'nullable|exists:lignes_budgetaires,id',

        'deplacements' => 'required|array|min:1',
        'deplacements.*.date_debut' => 'required|date',
        'deplacements.*.date_fin' => 'required|date|after_or_equal:deplacements.*.date_debut',
        'deplacements.*.heure' => 'nullable|string',
        'deplacements.*.ligne_budgetaire_id' => 'nullable|exists:lignes_budgetaires,id',
        'deplacements.*.ville' => 'nullable|string',
        'deplacements.*.taux' => 'required|numeric|min:0',
    ]);

    // Créer la mission de groupe
    $groupTrip = GroupTrip::create([
        'titre' => $validated['titre'],
        'lieu' => $validated['lieu'],
        'date_depart' => $validated['date_depart'],
        'date_retour' => $validated['date_retour'],
        'type_mission' => $validated['type_mission'] ?? null,
        'moyen_transport' => $validated['moyen_transport'] ?? null,
        'ligne_budgetaire_id' => $validated['ligne_budgetaire_id'] ?? null,
    ]);

    // Enregistrer les déplacements
    foreach ($validated['deplacements'] as $dep) {
        $groupTrip->deplacements()->create([
            'date_debut' => $dep['date_debut'],
            'date_fin' => $dep['date_fin'],
            'heure' => $dep['heure'] ?? null,
            'ligne_budgetaire_id' => $dep['ligne_budgetaire_id'] ?? null,
            'ville' => $dep['ville'] ?? null,
            'taux' => $dep['taux'],
        ]);
    }

    return redirect()->route('group-trips.index')->with('success', 'Mission enregistrée avec succès');
}


    // Affichage d'une mission (avec tous les participants et utilisateurs)
    public function show($id)
    {
        $groupTrip = GroupTrip::with('participants')->findOrFail($id);
        $users = User::all();

        return inertia('groupTrip/Show', [
            'groupTrip' => $groupTrip,
            'allUsers' => $users
        ]);
    }

    // Ajouter un participant
    public function addParticipant(Request $request, $id)
    {
        $groupTrip = GroupTrip::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string',
            'distance_parcourue' => 'required|integer|min:0',
            'nombre_repas' => 'nullable|integer|min:0'
        ]);

        $groupTrip->participants()->attach($request->user_id, [
            'role' => $request->role,
            'distance_parcourue' => $request->distance_parcourue,
            'nombre_repas' => $request->nombre_repas ?? 0,
        ]);

        return back()->with('success', 'Participant ajouté.');
    }

    // Calcul automatique des primes
    public function calculatePrimes($id)
    {
        $groupTrip = GroupTrip::with('participants')->findOrFail($id);

        $nbJours = Carbon::parse($groupTrip->date_depart)->diffInDays(Carbon::parse($groupTrip->date_retour)) + 1;
        $base = 100;
        $indemnite_repas = 15;

        foreach ($groupTrip->participants as $user) {
            $distance = $user->pivot->distance_parcourue ?? 0;
            $repas = $user->pivot->nombre_repas ?? 0;

            $coefficient = match ($user->pivot->role) {
                'chef de mission' => 1.5,
                'assistant' => 1.2,
                default => 1.0,
            };

            $prime = $base * $coefficient * ($distance / 100) * ($nbJours / 2);
            $prime += $repas * $indemnite_repas;

            $groupTrip->participants()->updateExistingPivot($user->id, [
                'prime_calculee' => round($prime, 2)
            ]);
        }

        return back()->with('success', 'Primes recalculées.');
    }
}
