<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonnelUpdateRequest extends FormRequest
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
            'nom' => ['required', 'string'],
            'prenom' => ['required', 'string'],
            'num_cin' => ['required', 'string'],
            'num_ppr' => ['required', 'string'],
            'grade' => ['required', 'string'],
            'echelle' => ['required', 'string'],
            'groupe' => ['required', 'string'],
            'taux_indemnite' => ['required'],
            'montant_indemnite' => ['required'],
            'banque_rib' => ['required'],
            'guichet_rib' => ['required'],
            'num_compte_rib' => ['required'],
            'code_rib' => ['required'],
            'residence' => ['required', 'string'],
            'creance' => ['required'],
            'suffix' => ['required'], 'string',
            'situation_familiale' => ['required', 'string'],
            'vehicule.nom' => ['nullable', 'string'],
            'vehicule.matricule' => ['nullable', 'string'],
            'vehicule.puissance' => ['nullable'],
            'vehicule.limite_annuel' => ['nullable'],
        ];
    }
}
