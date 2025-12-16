<?php

namespace App\Models;

use App\Enums\Symbol;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'symbol',
        'amount',
        'locked_amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'locked_amount' => 'decimal:8',
            'symbol' => Symbol::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAvailableAmountAttribute(): float
    {
        return (float) ($this->amount - $this->locked_amount);
    }
}
