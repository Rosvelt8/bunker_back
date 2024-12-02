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
            $table->id(); // ID unique auto-incrémenté
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // ID de l'utilisateur
            $table->enum('document_type', ['id_card', 'location_map', 'tax_identifier'])->comment('Type de document'); // Type de document
            $table->string('document_path')->comment('Chemin du fichier téléversé'); // Chemin du document
            $table->timestamps(); // Champs "created_at" et "updated_at"
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
