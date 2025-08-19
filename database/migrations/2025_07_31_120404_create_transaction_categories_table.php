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
        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the category or subcategory (e.g., "Income", "Rents")
            $table->unsignedBigInteger('parent_id')->nullable(); // Parent category ID for subcategories, null for top-level categories
            $table->text('description')->nullable(); // Explanation of the category/subcategory
            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('transaction_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_categories');
    }
};
