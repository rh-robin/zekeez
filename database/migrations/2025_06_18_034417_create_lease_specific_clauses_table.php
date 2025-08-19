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
        Schema::create('lease_specific_clauses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lease_id')->nullable();
            $table->boolean('joint_several_liability_clause')->nullable();
            $table->boolean('termination_clause')->nullable();
            $table->text('termination_clause_grounds')->nullable();
            $table->text('destination_clause_type_of_use')->nullable();
            $table->boolean('key_money_right_to_lease')->nullable();
            $table->decimal('key_money_amount',10,2)->nullable();
            $table->string('key_money_legal_qualification')->nullable();
            $table->text('right_to_lease_existence')->nullable();
            $table->text('right_to_lease_conditions_assignment')->nullable();
            $table->decimal('right_to_lease_value', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('lease_id')->references('id')->on('leases')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lease_specific_clauses');
    }
};
