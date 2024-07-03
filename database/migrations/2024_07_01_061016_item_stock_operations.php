<?php

use App\Models\Item;
use App\Models\ItemStock;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_stock_operations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['add', 'deduct'])->default('add')->nullable(false);
            $table->unsignedBigInteger('item_id')->nullable();
            $table->foreign('item_id')->references('id')->on('items');
            $table->double('qty');
            $table->foreignId('created_by')->nullable()->references('id')->on('employees')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_stock_operations');
    }
};
