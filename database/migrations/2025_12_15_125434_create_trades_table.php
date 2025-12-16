<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $schemaTable = 'trades';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->schemaTable, function (Blueprint $table) {
            $table->id();
            $table->foreignId('buy_order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('sell_order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('symbol', 10);
            $table->decimal('price', 20, 8);
            $table->decimal('amount', 20, 8);
            $table->decimal('buyer_commission', 20, 8)->default(0);
            $table->decimal('seller_commission', 20, 8)->default(0);
            $table->decimal('total_value', 20, 8);
            $table->timestamps();

            $table->index('buy_order_id');
            $table->index('sell_order_id');
            $table->index('buyer_id');
            $table->index('seller_id');
            $table->index('symbol');
            $table->index(['symbol', 'created_at']);
            $table->index(['buyer_id', 'created_at']);
            $table->index(['seller_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->schemaTable);
    }
};
