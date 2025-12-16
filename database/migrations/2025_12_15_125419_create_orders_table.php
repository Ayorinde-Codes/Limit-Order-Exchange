<?php

use App\Enums\OrderSide;
use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $schemaTable = 'orders';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->schemaTable, function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('symbol', 10);
            $table->enum('side', [OrderSide::BUY->value, OrderSide::SELL->value]);
            $table->decimal('price', 20, 8);
            $table->decimal('amount', 20, 8);
            $table->decimal('filled_amount', 20, 8)->default(0);
            $table->tinyInteger('status')->default(OrderStatus::OPEN->value);
            $table->timestamps();

            $table->index('user_id');
            $table->index(['symbol', 'side', 'status']);
            $table->index(['symbol', 'side', 'status', 'price']);
            $table->index(['status', 'created_at']);
            $table->index(['symbol', 'status', 'price', 'created_at']);
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
