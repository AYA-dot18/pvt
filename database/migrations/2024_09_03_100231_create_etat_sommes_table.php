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
        Schema::disableForeignKeyConstraints();

        Schema::create('etat_sommes', function (Blueprint $table) {
            $table->id();
            $table->float('montant');
            $table->enum('type', ["ik","prime"]);
            $table->string('vehicule_nom', 191)->nullable();
            $table->string('vehicule_matricule', 191)->nullable();
            $table->float('vehicule_puissance')->nullable();
            $table->float('vehicule_limite_annuel')->nullable();
            $table->text('etat_somme_path');
            $table->integer('mois');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etat_sommes');
    }
};
