<?php

namespace App\Actions\Order;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetOrderHistoryAction
{
    public function execute(User $user, ?string $status = null, ?string $symbol = null): LengthAwarePaginator
    {
        $query = Order::where('user_id', $user->id)
            ->with(['buyTrades', 'sellTrades']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($symbol) {
            $query->where('symbol', $symbol);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(15);
    }
}

