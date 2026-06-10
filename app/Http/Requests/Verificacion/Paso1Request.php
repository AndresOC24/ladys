<?php

namespace App\Http\Requests\Verificacion;

use Illuminate\Foundation\Http\FormRequest;

class Paso1Request extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:20', 'regex:/^[0-9+\s-]+$/'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, guiones y el signo +.',
            'fecha_nacimiento.before_or_equal' => 'Debes ser mayor de 18 años.',
        ];
    }
}
