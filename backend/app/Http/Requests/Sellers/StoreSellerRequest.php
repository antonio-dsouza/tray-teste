<?php

namespace App\Http\Requests\Sellers;

use Illuminate\Foundation\Http\FormRequest;

class StoreSellerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:sellers,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'O nome do vendedor deve ter pelo menos 2 caracteres.',
            'email.unique' => 'Já existe um vendedor com este email.',
        ];
    }
}
