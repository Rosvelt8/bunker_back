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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('idorder');
            $table->foreignId('user_id')->on('users')->cascadeOnDelete();
            $table->decimal('total_price', 10, 2);
            $table->decimal('delivery_cost', 10, 2);
            $table->string('delivery_location');
            $table->text('instructions')->nullable();
            $table->enum('status', ['on_hold','paid', 'in_progress', 'ready', 'depot', 'in_delivery', 'booked', 'cancelled'])->default('on_hold');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
