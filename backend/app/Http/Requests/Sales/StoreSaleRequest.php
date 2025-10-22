<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seller_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'sold_at' => ['required', 'date'],
        ];
    }


    public function messages(): array
    {
        return [
            'seller_id.required' => 'O campo vendedor é obrigatório.',
            'amount.required' => 'O campo valor da venda é obrigatório.',
            'amount.min' => 'O valor da venda deve ser pelo menos 0,01.',
            'sold_at.required' => 'O campo data da venda é obrigatório.',
        ];
    }
}
