<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    public function lockBalanceForBuyOrder(User $user, string $symbol, float $amount, float $price): bool
    {
        return DB::transaction(function () use ($user, $amount, $price) {
            $user = User::lockForUpdate()->find($user->id);
            $requiredBalance = $amount * $price;

            if ($user->balance < $requiredBalance) {
                return false;
            }

            $user->balance -= $requiredBalance;

            return $user->save();
        });
    }

    public function lockAssetForSellOrder(User $user, string $symbol, float $amount): bool
    {
        return DB::transaction(function () use ($user, $symbol, $amount) {
            $asset = Asset::lockForUpdate()
                ->where('user_id', $user->id)
                ->where('symbol', $symbol)
                ->first();

            if (! $asset || $asset->available_amount < $amount) {
                return false;
            }

            $asset->locked_amount += $amount;

            return $asset->save();
        });
    }

    public function releaseLockedBalance(User $user, float $amount): void
    {
        DB::transaction(function () use ($user, $amount) {
            $user = User::lockForUpdate()->find($user->id);
            $user->balance += $amount;
            $user->save();
        });
    }

    public function releaseLockedAsset(User $user, string $symbol, float $amount): void
    {
        DB::transaction(function () use ($user, $symbol, $amount) {
            $asset = Asset::lockForUpdate()
                ->where('user_id', $user->id)
                ->where('symbol', $symbol)
                ->first();

            if ($asset) {
                $asset->locked_amount -= $amount;
                $asset->save();
            }
        });
    }
}
