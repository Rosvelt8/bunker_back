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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('status', ['seller', 'delivery_person', 'customer', 'admin'])->default('customer');
            $table->foreignId('city_id')->references('id')->nullable()->on('cities');
            $table->foreignId('country_id')->references('id')->nullable()->on('countries');
            $table->boolean('is_validated')->default(false);
            $table->boolean('is_delivery_request')->default(false);
            $table->boolean('is_saler_request')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
