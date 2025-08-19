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
        Schema::create('contact_entity_representatives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->string('salutation')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('quality')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('siren')->nullable();
            $table->string('website_url')->nullable();
            $table->timestamps();

            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_representatives');
    }
};
