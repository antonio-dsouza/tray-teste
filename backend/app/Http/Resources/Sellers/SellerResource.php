<?php

namespace App\Http\Resources\Sellers;

use App\Http\Resources\Sales\SaleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'sales' => $this->when($this->relationLoaded('sales'), function () {
                return SaleResource::collection($this->sales);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
