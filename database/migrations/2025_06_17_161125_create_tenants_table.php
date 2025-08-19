<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->enum('type', ['individual', 'legal_entity'])->nullable();
            $table->string('category')->nullable();
            $table->string('salutation')->nullable();
            $table->string('company_name')->nullable();
            $table->string('legal_status')->nullable();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->text('additional_address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('entity_id')->references('id')->on('entities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
