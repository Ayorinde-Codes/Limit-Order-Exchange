<?php

namespace App\Actions\Order;

use App\Enums\OrderSide;
use App\Http\Resources\OrderResource;
use App\Models\Order;

class GetOrdersAction
{
    public function execute(?string $symbol = null): array
    {
        $query = Order::open()
            ->with('user:id,name')
            ->orderBy('price', 'asc')
            ->orderBy('created_at', 'asc');

        if ($symbol) {
            $query->forSymbol($symbol);
        }

        $orders = $query->get();

        $buyOrders = $orders->filter(fn($order) => $order->side === OrderSide::BUY)->values();
        $sellOrders = $orders->filter(fn($order) => $order->side === OrderSide::SELL)->values();

        return [
            'buy_orders' => OrderResource::collection($buyOrders)->toArray(request()),
            'sell_orders' => OrderResource::collection($sellOrders)->toArray(request()),
        ];
    }
}

