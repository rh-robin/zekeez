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
        Schema::create('lease_property', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lease_id');
            $table->unsignedBigInteger('property_id');
            $table->string('property_type'); // 'App\Models\Building' or 'App\Models\Unit'
            $table->timestamps();

            $table->foreign('lease_id')->references('id')->on('leases')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lease_property');
    }
};
