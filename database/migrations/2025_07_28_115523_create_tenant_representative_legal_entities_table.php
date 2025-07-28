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
            $table->unsignedBigInteger('tenant_id');
            $table->string('salutation')->nullable();
            $table->string('name');
            $table->string('first_name');
            $table->string('quality');
            $table->string('date_of_birth');
            $table->string('place_of_birth');
            $table->text('address')->nullable();
            $table->text('additional_address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city');
            $table->string('country');
            $table->string('phone');
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
