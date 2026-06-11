<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RolRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:255', Rule::unique('roles', 'nombre')->ignore($id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
