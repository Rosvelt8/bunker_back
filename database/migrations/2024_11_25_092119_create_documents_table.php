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
        Schema::create('documents', function (Blueprint $table) {
            $table->id('iddocument');
            $table->foreignId('user_id')->on('users')->cascadeOnDelete();
            $table->enum('document_type', ['id_card', 'location_map', 'tax_identifier'])->comment('Type de document');
            $table->boolean('status')->default(false);            // Type de document
            $table->string('document_path')->comment('Chemin du fichier téléversé');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
