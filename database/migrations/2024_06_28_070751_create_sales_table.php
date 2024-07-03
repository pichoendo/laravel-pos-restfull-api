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
            $table->unsignedBigInteger('employee_by')->nullable();
            $table->foreign('employee_by')->references('id')->on('employees');
            $table->unsignedBigInteger('member_by')->nullable();
            $table->foreign('member_by')->references('id')->on('members');
            $table->double('discount');
            $table->double('tax');
            $table->double('sub_total');
            $table->double('total');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('employees');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('employees');;
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
