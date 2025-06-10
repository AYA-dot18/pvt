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

        Schema::create('deplacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trajet_id')->constrained();
            $table->foreignId('prime_id')->constrained();
            $table->foreignId('ligne_budgetaire_id')->constrained();
            $table->foreignId('etat_somme_id')->nullable()->constrained();
            $table->foreignId('ik_id')->nullable()->constrained();
            $table->foreignId('tgr_id')->nullable()->constrained();
            $table->float('montant');
            $table->unsignedInteger('nombre_taux')->nullable();
            $table->text('ordre_mission_path')->nullable();
            $table->integer('mois');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('repas');
            $table->string('heure_depart');
            $table->string('heure_retour');

            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deplacements');
    }
};
