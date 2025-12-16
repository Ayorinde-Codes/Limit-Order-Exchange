<?php

namespace App\Models;

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use App\Enums\Symbol;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'symbol',
        'side',
        'price',
        'amount',
        'filled_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:8',
            'amount' => 'decimal:8',
            'filled_amount' => 'decimal:8',
            'status' => OrderStatus::class,
            'side' => OrderSide::class,
            'symbol' => Symbol::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function buyTrades()
    {
        return $this->hasMany(Trade::class, 'buy_order_id');
    }

    public function sellTrades()
    {
        return $this->hasMany(Trade::class, 'sell_order_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', OrderStatus::OPEN);
    }

    public function scopeForSymbol($query, Symbol|string $symbol)
    {
        $symbolValue = $symbol instanceof Symbol ? $symbol->value : $symbol;

        return $query->where('symbol', $symbolValue);
    }

    public function scopeBuyOrders($query)
    {
        return $query->where('side', OrderSide::BUY);
    }

    public function scopeSellOrders($query)
    {
        return $query->where('side', OrderSide::SELL);
    }

    public function isOpen(): bool
    {
        return $this->status === OrderStatus::OPEN;
    }

    public function getRemainingAmountAttribute(): float
    {
        return (float) ($this->amount - $this->filled_amount);
    }
}
