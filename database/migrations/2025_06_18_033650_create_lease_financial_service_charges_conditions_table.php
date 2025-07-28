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
        Schema::create('lease_financial_service_charges_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lease_id');

            // Service Charge Details
            $table->string('type_of_service_charges');
            $table->decimal('monthly_flat_rate_amount', 10, 2);
            $table->string('fixed_charges_included')->nullable();
            $table->decimal('monthly_provision_actual_charges', 10, 2);
            $table->text('types_of_actual_charges')->nullable();
            $table->text('procedures_regularization_actual_charges')->nullable();
            $table->text('distribution_charges_co_tenants')->nullable();

            // Specific Charges and Taxes Allocations
            $table->string('property_tax_allocation');
            $table->string('property_tax_allocation_other')->nullable();
            $table->string('co_ownership_charges_allocation');
            $table->string('co_ownership_charges_allocation_other')->nullable();
            $table->string('insurance_allocation');
            $table->string('insurance_allocation_other')->nullable();
            $table->string('maintenance_repairs_allocation')->nullable();
            $table->string('maintenance_repairs_allocation_other')->nullable();
            $table->string('taxes_fees_allocation')->nullable();
            $table->string('taxes_fees_allocation_other')->nullable();
            $table->string('amount_of_security_deposit')->nullable();
            
            $table->timestamps();
            
            $table->foreign('lease_id')->references('id')->on('leases')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_charge_conditions');
    }
};
