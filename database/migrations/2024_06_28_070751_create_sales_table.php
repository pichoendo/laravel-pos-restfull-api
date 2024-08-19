<?php

use App\Models\Employee;
use App\Models\Member;
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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code');
            $table->unsignedBigInteger('managed_by')->nullable();
            $table->foreign('managed_by')->references('id')->on('employees');
            $table->unsignedBigInteger('member_by')->nullable();
            $table->foreign('member_by')->references('id')->on('members');
            $table->double('discount');
            $table->double('tax');
            $table->double('sub_total');
            $table->double('total');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
