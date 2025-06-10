<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaeStoreRequest extends FormRequest
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
            'date_depart' => ['required', 'date'],
            'date_retour' => ['required', 'date'],
            'destination' => ['required', 'string'],
        ];
    }
}
