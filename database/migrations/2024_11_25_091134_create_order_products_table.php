<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->id('idproductorder');
            $table->foreignId('order_id')->references('idorder')->on('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->references('idproduct')->on('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_products');
    }
};
