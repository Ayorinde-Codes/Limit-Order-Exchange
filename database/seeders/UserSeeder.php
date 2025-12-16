<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // User 1: Buyer
        User::factory()->create([
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'balance' => 100000.00,
        ]);

        // User 2: Buyer
        User::factory()->create([
            'name' => 'Alan Cooper',
            'email' => 'alan@example.com',
            'balance' => 50000.00,
        ]);

        // User 3: Seller with BTC and ETH
        $seller1 = User::factory()->create([
            'name' => 'Mike Johnson',
            'email' => 'mike@example.com',
            'balance' => 30000.00,
        ]);
        Asset::factory()->btc()->withAmount(2.0)->create(['user_id' => $seller1->id]);
        Asset::factory()->eth()->withAmount(3.0)->create(['user_id' => $seller1->id]);

        // User 4: Seller with BTC and ETH
        $seller2 = User::factory()->create([
            'name' => 'Sarah Davis',
            'email' => 'sarah@example.com',
            'balance' => 20000.00,
        ]);
        Asset::factory()->btc()->withAmount(3.0)->create(['user_id' => $seller2->id]);
        Asset::factory()->eth()->withAmount(3.0)->create(['user_id' => $seller2->id]);
    }
}
