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
        Schema::create('tenant_representative_legal_entities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('salutation')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('quality')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->text('additional_address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('siret_siren_number')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_representative_legal_entities');
    }
};
