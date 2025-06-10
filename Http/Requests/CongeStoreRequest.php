<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CongeStoreRequest extends FormRequest
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
            'personnel_id' => ['required','integer'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date'],
            'type' => ['required', 'string'],
            'remarque' => ['nullable'],
        ];
    }
}
