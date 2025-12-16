<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'symbol' => $this->symbol->value,
            'amount' => $this->amount,
            'locked_amount' => $this->locked_amount,
            'available' => $this->available_amount,
        ];
    }
}
