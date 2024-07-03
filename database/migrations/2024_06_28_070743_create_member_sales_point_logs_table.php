<?php

use App\Models\Member;
use App\Models\Sales;
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
        Schema::create('member_sales_point_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('description');
            $table->double('point');
            $table->integer('type');
            $table->foreignIdFor(Sales::class)->nullable()->cascadeOnDelete();
            $table->foreignIdFor(Member::class)->nullable()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->references('id')->on('employees')->cascadeOnDelete();
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_sales_point_logs');
    }
};
