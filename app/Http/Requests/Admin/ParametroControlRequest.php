<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParametroControlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->get('id') ?? request()->route('id');

        return [
            'categoria' => ['required', 'string', 'max:255'],
            'clave' => ['required', 'string', 'max:255', Rule::unique('parametros_control', 'clave')->ignore($id)],
            'valor' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
        ];
    }
}
