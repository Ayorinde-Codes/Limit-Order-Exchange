<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $assets = [];
        
        if ($this->relationLoaded('assets') && $this->assets) {
            foreach ($this->assets as $asset) {
                $assets[] = [
                    'symbol' => $asset->symbol->value,
                    'amount' => (float) $asset->amount,
                    'locked_amount' => (float) $asset->locked_amount,
                    'available' => (float) $asset->available_amount,
                ];
            }
        }
        
        return [
            'balance' => (float) $this->balance,
            'assets' => $assets,
        ];
    }
}

