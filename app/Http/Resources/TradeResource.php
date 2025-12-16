<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'symbol' => $this->symbol->value,
            'price' => $this->price,
            'amount' => $this->amount,
            'total_value' => $this->total_value,
            'buyer_commission' => $this->buyer_commission,
            'seller_commission' => $this->seller_commission,
            'buy_order_id' => $this->buy_order_id,
            'sell_order_id' => $this->sell_order_id,
            'buyer' => new UserResource($this->whenLoaded('buyer')),
            'seller' => new UserResource($this->whenLoaded('seller')),
            'created_at' => $this->created_at,
        ];
    }
}
