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
        Schema::create('unit_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');
            $table->integer('num_of_living_room')->nullable();
            $table->integer('num_of_bedroom')->nullable();
            $table->integer('num_of_bathroom')->nullable();
            $table->integer('num_of_toilet')->nullable();
            $table->string('habitable_area')->nullable();
            $table->string('commercial_area')->nullable();
            $table->string('sales_area')->nullable();
            $table->bigInteger('storage_area')->nullable();
            $table->string('office_space')->nullable();
            $table->string('reserve_area')->nullable();
            $table->string('sanitary_area')->nullable();
            $table->string('professional_surface')->nullable();
            $table->string('reception_area')->nullable();
            $table->string('waiting_room_area')->nullable();
            $table->string('consultation_area')->nullable();
            $table->string('parking_type')->nullable();
            $table->string('parking_lease_length')->nullable();
            $table->string('parking_lease_width')->nullable();
            $table->string('parking_lease_height')->nullable();
            $table->string('property_type')->nullable();
            $table->string('other_type_of_lot')->nullable();
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_details');
    }
};
