<?php

namespace App\Http\Requests\Verificacion;

use Illuminate\Foundation\Http\FormRequest;

class Paso3Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'selfie' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'selfie.required' => 'No se recibió la captura de tu rostro. Intenta nuevamente.',
            'selfie.mimes' => 'La captura debe ser JPG o PNG.',
            'selfie.max' => 'La captura no puede superar los 5 MB.',
        ];
    }
}
