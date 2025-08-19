<?php

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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->unique();
            $table->string('entry_reference')->nullable();
            $table->date('booking_date');
            $table->date('value_date');
            $table->decimal('amount', 15, 2);
            $table->string('currency');
            $table->string('creditor_name')->nullable();
            $table->string('debtor_name')->nullable();
            $table->text('remittance_information')->nullable();
            $table->string('bank_transaction_code')->nullable();
            $table->string('proprietary_bank_transaction_code')->nullable();
            $table->string('internal_transaction_id')->nullable();
            $table->foreignId('entity_id')->nullable()->constrained('entities')->onDelete('set null');
            $table->foreignId('building_id')->nullable()->constrained('buildings')->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->foreignId('lease_id')->nullable()->constrained('leases')->onDelete('set null');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('transaction_categories')->onDelete('set null');
            $table->enum('status', ['to_categorize', 'to_validate', 'validated', 'archived'])->default('to_categorize');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
