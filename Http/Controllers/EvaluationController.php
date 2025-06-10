<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use Illuminate\Http\Request;
use App\Models\Personnel;
use App\Models\Prime;
use Inertia\Inertia;


 
class EvaluationController extends Controller
{
    public function index()
    {
        $evaluations = Evaluation::with(['personnel', 'prime'])->latest()->get();
        $personnels = Personnel::all(['id', 'nom', 'prenom']);
        $primes = Prime::all(['id', 'type', 'montant', 'personnel_id']);

        return Inertia::render('evaluation/Liste', [
            'personnels' => $personnels,
            'primes' => $primes,
            'evaluations' => $evaluations,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'personnel_id' => 'required|exists:personnels,id',
            'prime_id' => 'required|exists:primes,id',
            'resultat_deplacement' => 'required|string',
            'organisation' => 'required|integer|min:0|max:10',
            'respect_horaires' => 'required|integer|min:0|max:10',
            'gestion_couts' => 'required|integer|min:0|max:10',
            'commentaire_deplacement' => 'nullable|string',
            'ponctualite' => 'required|integer|min:0|max:10',
            'communication' => 'required|integer|min:0|max:10',
            'professionnalisme' => 'required|integer|min:0|max:10',
            'autonomie' => 'required|integer|min:0|max:10',
            'commentaire_personnel' => 'nullable|string',
            'justificatif' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $justificatifPath = null;
        if ($request->hasFile('justificatif')) {
            $justificatifPath = $request->file('justificatif')->store('justificatifs');
        }

        Evaluation::create([
            'personnel_id' => $validated['personnel_id'],
            'prime_id' => $validated['prime_id'],
            'resultat_deplacement' => $validated['resultat_deplacement'],
            'organisation' => $validated['organisation'],
            'respect_horaires' => $validated['respect_horaires'],
            'gestion_couts' => $validated['gestion_couts'],
            'commentaire_deplacement' => $validated['commentaire_deplacement'] ?? null,
            'ponctualite' => $validated['ponctualite'],
            'communication' => $validated['communication'],
            'professionnalisme' => $validated['professionnalisme'],
            'autonomie' => $validated['autonomie'],
            'commentaire_personnel' => $validated['commentaire_personnel'] ?? null,
            'justificatif_path' => $justificatifPath,
        ]);

        // ✅ Redirection vers la bonne route
        return redirect()->route('evaluations.liste')->with('success', 'Évaluation enregistrée avec succès');
    }
public function create()
{
    return Inertia::render('evaluation/Create');
}

    public function liste()
    {
        $evaluations = Evaluation::with(['personnel', 'prime'])->get();

        return Inertia::render('evaluation/Liste', [
    'evaluations' => $evaluations,
]);

    }
    public function show(Evaluation $evaluation)
{
    $evaluation->load(['personnel', 'prime']);
    return Inertia::render('Evaluations/Show', [
        'evaluation' => $evaluation,
    ]);
}

public function edit(Evaluation $evaluation)
{
    $personnels = Personnel::all(['id', 'nom', 'prenom']);
    $primes = Prime::all(['id', 'type', 'montant', 'personnel_id']);

    return Inertia::render('Evaluations/Edit', [
        'evaluation' => $evaluation,
        'personnels' => $personnels,
        'primes' => $primes,
    ]);
}

public function update(Request $request, Evaluation $evaluation)
{
    $validated = $request->validate([
        'personnel_id' => 'required|exists:personnels,id',
        'prime_id' => 'required|exists:primes,id',
        'resultat_deplacement' => 'required|string',
        'organisation' => 'required|integer|min:0|max:10',
        'respect_horaires' => 'required|integer|min:0|max:10',
        'gestion_couts' => 'required|integer|min:0|max:10',
        'commentaire_deplacement' => 'nullable|string',
        'ponctualite' => 'required|integer|min:0|max:10',
        'communication' => 'required|integer|min:0|max:10',
        'professionnalisme' => 'required|integer|min:0|max:10',
        'autonomie' => 'required|integer|min:0|max:10',
        'commentaire_personnel' => 'nullable|string',
    ]);

    $evaluation->update($validated);

    return redirect()->route('evaluation.liste')->with('success', 'Évaluation mise à jour.');
}

public function destroy(Evaluation $evaluation)
{
    $evaluation->delete();
    return redirect()->route('evaluation.Liste')->with('success', 'Évaluation supprimée.');
}

}
