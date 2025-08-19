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
        Schema::create('lease_tenant', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lease_id');
            $table->unsignedBigInteger('tenant_id');
            $table->timestamps();

            $table->foreign('lease_id')->references('id')->on('leases')->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lease_tenant');
    }
};
