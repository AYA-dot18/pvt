<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrimeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'personnel_id' => 'required|exists:personnels,id',
            'type' => 'required|string|max:191',
            'mois' => 'nullable|array',
            'creance_cree' => 'required|numeric',
            'changetaux' => 'nullable|string|max:191',
            'montant' => 'required|numeric',
            'nombre_taux' => 'nullable|integer',
            'remarque' => 'nullable|string',
            'deplacements' => 'nullable|array',
            'deplacements.*.trajet_id' => 'required|exists:trajets,id',
            'deplacements.*.ligne_budgetaire_id' => 'required|exists:ligne_budgetaires,id',
            'deplacements.*.montant' => 'required|numeric',
            'deplacements.*.nombre_taux' => 'nullable|integer',
            'deplacements.*.date_debut' => 'required|date',
            'deplacements.*.date_fin' => 'required|date',
            'deplacements.*.repas' => 'required|integer',
            'deplacements.*.ik' =>'nullable|array',
            'deplacements.*.has_ik' =>'nullable',
        ];
    }
}
