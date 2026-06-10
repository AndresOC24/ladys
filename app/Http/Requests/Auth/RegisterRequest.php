<?php

namespace App\Http\Requests\Auth;

use App\Models\Rol;
use App\Models\Usuaria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Usuaria::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'rol_id' => [
                'required',
                Rule::exists('roles', 'id')->whereNot('nombre', 'administrador'),
            ],
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
            'rol_id.required' => 'Debes seleccionar si te registras como pasajera o conductora.',
            'rol_id.exists' => 'El rol seleccionado no es válido.',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, guiones y el signo +.',
            'fecha_nacimiento.before_or_equal' => 'Debes ser mayor de 18 años para registrarte.',
        ];
    }

    /**
     * Roles available for self-registration (excludes administrador).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Rol>
     */
    public static function rolesDisponibles()
    {
        return Rol::whereNot('nombre', 'administrador')->orderBy('nombre')->get();
    }
}
