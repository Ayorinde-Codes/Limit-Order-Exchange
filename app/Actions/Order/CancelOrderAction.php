<?php

namespace App\Actions\Order;

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use App\Enums\Symbol;
use App\Models\Order;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelOrderAction
{
    public function __construct(
        private BalanceService $balanceService
    ) {}

    public function execute(Order $order, User $user): Order
    {
        if ($order->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'order' => 'Unauthorized',
            ]);
        }

        if (!$order->isOpen()) {
            throw ValidationException::withMessages([
                'order' => 'Order cannot be cancelled',
            ]);
        }

        return DB::transaction(function () use ($order, $user) {
            $order->status = OrderStatus::CANCELLED;
            $order->save();

            // Release locked balance/assets
            if ($order->side === OrderSide::BUY) {
                $this->balanceService->releaseLockedBalance($user, $order->amount * $order->price);
            } else {
                $this->balanceService->releaseLockedAsset($user, $order->symbol->value, (float) $order->amount);
            }

            return $order;
        });
    }
}

