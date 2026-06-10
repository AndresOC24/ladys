<?php

namespace App\Http\Requests\Verificacion;

use Illuminate\Foundation\Http\FormRequest;

class Paso2Request extends FormRequest
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
            'anverso' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'reverso' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'anverso.required' => 'Debes subir la imagen del anverso de tu cédula.',
            'reverso.required' => 'Debes subir la imagen del reverso de tu cédula.',
            '*.mimes' => 'La imagen debe ser JPG o PNG.',
            '*.max' => 'La imagen no puede superar los 5 MB.',
        ];
    }
}
