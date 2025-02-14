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
        Schema::create('saler_products', function (Blueprint $table) {
            $table->id('idsalerproduct');
            $table->foreignId('product_id')->references('idproduct')->on('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->foreignId('saler_id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saler_products');
    }
};
