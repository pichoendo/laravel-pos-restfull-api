<?php

use App\Models\ItemStock;
use App\Models\SalesItem;
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
        Schema::create('item_stock_flows', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['add', 'deduct'])->default('add')->nullable(false);
            $table->nullableMorphs('source');
            $table->unsignedBigInteger('item_stock_id')->nullable();
            $table->foreign('item_stock_id')->references('id')->on('item_stocks');
            $table->double('qty');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_stock_flows');
    }
};
