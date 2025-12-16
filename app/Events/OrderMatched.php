<?php

namespace App\Events;

use App\Models\Trade;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMatched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Trade $trade,
        public User $buyer,
        public User $seller
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->buyer->id),
            new PrivateChannel('user.'.$this->seller->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.matched';
    }

    public function broadcastWith(): array
    {
        return [
            'trade' => [
                'id' => $this->trade->id,
                'symbol' => $this->trade->symbol->value,
                'price' => $this->trade->price,
                'amount' => $this->trade->amount,
                'total_value' => $this->trade->total_value,
                'created_at' => $this->trade->created_at,
            ],
            'buy_order_id' => $this->trade->buy_order_id,
            'sell_order_id' => $this->trade->sell_order_id,
            'buyer' => [
                'id' => $this->buyer->id,
                'balance' => $this->buyer->balance,
            ],
            'seller' => [
                'id' => $this->seller->id,
                'balance' => $this->seller->balance,
            ],
        ];
    }
}
