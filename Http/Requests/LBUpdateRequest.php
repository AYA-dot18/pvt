<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LBUpdateRequest extends FormRequest
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
            'nom' => ['required', 'max:191'],
            'type' => ['required', 'max:191'],
            'exercice' => ['required', 'max:191'],
            'chapitre' => ['required', 'max:191'],
            'article' => ['nullable'],
            'paragraphe' => ['nullable'],
            'ligne' => ['nullable'],
            'programme' => ['nullable'],
            'region' => ['nullable'],
            'projet' => ['nullable'],
            'montant_initial' => ['nullable'],
        ];
    }
}
