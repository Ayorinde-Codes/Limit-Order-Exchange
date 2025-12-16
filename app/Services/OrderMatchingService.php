<?php

namespace App\Services;

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use App\Models\Asset;
use App\Events\OrderMatched;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderMatchingService
{
    private const COMMISSION_RATE = 0.015; // 1.5%

    public function matchOrder(Order $newOrder): ?Trade
    {
        return DB::transaction(function () use ($newOrder) {
            $order = Order::lockForUpdate()->find($newOrder->id);
            
            if (!$order || !$order->isOpen()) {
                return null;
            }

            $counterOrder = $this->findMatchingOrder($order);

            if (!$counterOrder) {
                return null;
            }

            $counterOrder = Order::lockForUpdate()->find($counterOrder->id);
            
            if (!$counterOrder || !$counterOrder->isOpen()) {
                return null;
            }

            return $this->executeTrade($order, $counterOrder);
        });
    }

    /**
     * Find a matching order for sell order and buy order
     *
     * @param Order $order
     * @return Order|null
     */
    private function findMatchingOrder(Order $order): ?Order
    {
        if ($order->side === OrderSide::BUY) {
            return Order::open()
                ->forSymbol($order->symbol)
                ->sellOrders()
                ->where('price', '<=', $order->price)
                ->orderBy('price', 'asc')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->first();
        } else {
            return Order::open()
                ->forSymbol($order->symbol)
                ->buyOrders()
                ->where('price', '>=', $order->price)
                ->orderBy('price', 'desc')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->first();
        }
    }

    private function executeTrade(Order $order1, Order $order2): Trade
    {
        $buyOrder = $order1->side === OrderSide::BUY ? $order1 : $order2;
        $sellOrder = $order1->side === OrderSide::SELL ? $order1 : $order2;

        $buyer = User::lockForUpdate()->find($buyOrder->user_id);
        $seller = User::lockForUpdate()->find($sellOrder->user_id);

        $tradeAmount = min($buyOrder->remaining_amount, $sellOrder->remaining_amount);
        $tradePrice = $sellOrder->price;
        $totalValue = $tradeAmount * $tradePrice;

        $commission = $totalValue * self::COMMISSION_RATE;
        
        $buyerCommission = $commission;
        $sellerCommission = $commission / $tradePrice;

        // Update buyer balance and refund if trade price is less than order price
        $buyOrderLockedAmount = $tradeAmount * $buyOrder->price;
        $refundAmount = $buyOrderLockedAmount - $totalValue;
        $buyer->balance += $refundAmount;
        $buyer->balance -= $buyerCommission;
        $buyer->save();

        $sellerAsset = Asset::lockForUpdate()
            ->where('user_id', $seller->id)
            ->where('symbol', $sellOrder->symbol)
            ->first();

        if ($sellerAsset) {
            $sellerAsset->locked_amount -= $tradeAmount;
            $sellerAsset->amount -= ($tradeAmount + $sellerCommission);
            $sellerAsset->save();
        }

        $seller->balance += $totalValue;
        $seller->save();

        $buyerAsset = Asset::lockForUpdate()
            ->where('user_id', $buyer->id)
            ->where('symbol', $buyOrder->symbol)
            ->first();

        if (!$buyerAsset) {
            $buyerAsset = Asset::create([
                'user_id' => $buyer->id,
                'symbol' => $buyOrder->symbol,
                'amount' => 0,
                'locked_amount' => 0,
            ]);
        }

        $buyerAsset->amount += $tradeAmount;
        $buyerAsset->save();

        $buyOrder->filled_amount += $tradeAmount;
        $sellOrder->filled_amount += $tradeAmount;

        if ($buyOrder->filled_amount >= $buyOrder->amount) {
            $buyOrder->status = OrderStatus::FILLED;
        }
        if ($sellOrder->filled_amount >= $sellOrder->amount) {
            $sellOrder->status = OrderStatus::FILLED;
        }

        $buyOrder->save();
        $sellOrder->save();

        $trade = Trade::create([
            'buy_order_id' => $buyOrder->id,
            'sell_order_id' => $sellOrder->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'symbol' => $buyOrder->symbol,
            'price' => $tradePrice,
            'amount' => $tradeAmount,
            'buyer_commission' => $buyerCommission,
            'seller_commission' => $sellerCommission,
            'total_value' => $totalValue,
        ]);

        event(new OrderMatched($trade, $buyer, $seller));

        return $trade;
    }
}

