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
        Schema::table('products', function (Blueprint $table) {
            // Add new columns
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->decimal('originalPrice', 10, 2)->nullable();
            $table->decimal('discountedPrice', 10, 2)->nullable();
            $table->decimal('discount', 5, 2)->nullable();
            $table->boolean('isPromoted')->default(false);
            $table->foreignId('created_by')->on('users')->cascadeOnDelete();
            $table->foreignId('subCategory')->references('id')->on('sub_categories')->cascadeOnDelete();
            $table->string('image');
            $table->text('images')->nullable();
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('storage')->nullable();
            $table->text('sizes')->nullable();
            $table->text('colors')->nullable();
            $table->string('material')->nullable();
            $table->string('dimensions')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('sportType')->nullable();
            $table->string('level')->nullable();
            $table->decimal('rate', 3, 2)->nullable();
            $table->boolean('isNew')->default(false);
            $table->integer('salesCount')->default(0);
            $table->boolean('inStock')->default(true);
            $table->date('arrivalDate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
