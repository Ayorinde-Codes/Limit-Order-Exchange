<?php

namespace App\Http\Requests;

use App\Enums\OrderSide;
use App\Enums\Symbol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'symbol' => ['required', 'string', Rule::enum(Symbol::class)],
            'side' => ['required', 'string', Rule::enum(OrderSide::class)],
            'price' => 'required|numeric|min:0.01',
            'amount' => 'required|numeric|min:0.00000001',
        ];
    }
}
