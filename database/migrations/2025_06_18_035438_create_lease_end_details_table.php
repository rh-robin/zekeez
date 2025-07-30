<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lease_end_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lease_id')->nullable();
            $table->date('departure_date_of_the_tenant')->nullable();
            $table->decimal('deposit_to_be_returned', 10, 2)->nullable();
            $table->date('date_of_return_of_the_security_deposit')->nullable();
            $table->timestamps();
            
            $table->foreign('lease_id')->references('id')->on('leases')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lease_end_details');
    }
};
