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
        Schema::create('lease_rent_revision_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lease_id')->nullable();
            
            // Rent Revision Clause Details
            $table->string('frequency_of_rent_revision')->nullable();
            $table->date('date_of_last_rent_revision')->nullable();
            $table->string('reference_index')->nullable();
            $table->text('other_index_to_specify')->nullable();
            $table->string('index_reference_quarter')->nullable();
            $table->string('index_reference_year')->nullable();
            $table->decimal('reference_index_value', 10, 2)->nullable();
            $table->text('revision_formula')->nullable();
            $table->timestamps();

            $table->foreign('lease_id')->references('id')->on('leases')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_revision_conditions');
    }
};
