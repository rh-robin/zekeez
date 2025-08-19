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
        Schema::create('lease_term_effective_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lease_id')->nullable();


            $table->string('lease_type')->nullable();
            $table->string('furnished_lease_term_type')->nullable();
            $table->integer('mobility_lease_term')->nullable();
            $table->integer('unfurnished_lease_term')->nullable();
            $table->integer('commercial_lease_term')->nullable();
            $table->string('professional_lease_term')->nullable();
            $table->integer('parking_or_other_lease_term')->nullable();

            // Dates
            $table->date('lease_signing_date')->nullable();
            $table->date('lease_effective_date')->nullable();

            // Conditions
            $table->string('lease_renewal_conditions')->nullable();
            $table->string('other_lease_renewal_conditions')->nullable();

            $table->timestamps();

            $table->foreign('lease_id')->references('id')->on('leases')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lease_term_effective_dates');
    }
};
