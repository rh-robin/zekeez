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
        Schema::create('lease_zekeez_automations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('lease_id');
            // Rent Generation and Balance
            $table->date('generate_rents_from')->nullable();
            $table->boolean('has_tenant_balance')->nullable();
            $table->decimal('tenant_balance', 10, 2)->nullable();
            $table->boolean('automatic_rent_revision')->default(false);

            // Automation Settings
            $table->boolean('automatic_rent_receipt_sending')->default(false);
            $table->boolean('automatic_rent_call_sending')->default(false);
            $table->date('rent_call_sending_date')->nullable();
            $table->boolean('automatic_first_reminder_unpaid_rent')->default(false);
            $table->date('first_unpaid_rent_reminder_sending_date')->nullable();
            $table->boolean('automatic_second_reminder_unpaid_rent')->default(false);
            $table->date('second_unpaid_rent_reminder_sending_date')->nullable();
            $table->boolean('automatic_third_reminder_unpaid_rent')->default(false);
            $table->date('third_unpaid_rent_reminder_sending_date')->nullable();

            $table->timestamps();
            
            $table->foreign('lease_id')->references('id')->on('leases')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_automation_settings');
    }
};
