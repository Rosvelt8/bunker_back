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
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id('idsuggestion');
            $table->foreignId('order_id')->references('idorder')->on('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->on('users')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('suggestions');
    }
};
