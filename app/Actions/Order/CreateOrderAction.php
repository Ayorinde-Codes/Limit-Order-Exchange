<?php

namespace App\Actions\Order;

use App\Enums\OrderStatus;
use App\Jobs\MatchOrderJob;
use App\Models\Order;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrderAction
{
    public function __construct(
        private BalanceService $balanceService
    ) {}

    public function execute(array $data, User $user): Order
    {
        return DB::transaction(function () use ($data, $user) {
            if ($data['side'] === 'buy') {
                $requiredBalance = $data['amount'] * $data['price'];
                if (!$this->balanceService->lockBalanceForBuyOrder($user, $data['symbol'], $data['amount'], $data['price'])) {
                    throw ValidationException::withMessages([
                        'balance' => 'Insufficient balance',
                    ]);
                }
            } else {
                if (!$this->balanceService->lockAssetForSellOrder($user, $data['symbol'], $data['amount'])) {
                    throw ValidationException::withMessages([
                        'amount' => 'Insufficient asset amount',
                    ]);
                }
            }

            $order = Order::create([
                'user_id' => $user->id,
                'symbol' => $data['symbol'],
                'side' => $data['side'],
                'price' => $data['price'],
                'amount' => $data['amount'],
                'status' => OrderStatus::OPEN,
            ]);

            MatchOrderJob::dispatch($order);

            return $order->load('user:id,name');
        });
    }
}

