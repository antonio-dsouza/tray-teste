<?php

namespace App\Http\Resources\Sales;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'seller_id' => $this->seller_id,
            'seller' => $this->when($this->relationLoaded('seller'), function () {
                return [
                    'id' => $this->seller->id,
                    'name' => $this->seller->name,
                    'email' => $this->seller->email,
                    'created_at' => $this->seller->created_at,
                    'updated_at' => $this->seller->updated_at,
                ];
            }),
            'amount' => (float) $this->amount,
            'commission_amount' => (float) $this->commission_amount,
            'sold_at' => $this->sold_at?->toISOString(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
