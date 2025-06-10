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
       Schema::create('evaluations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('personnel_id')->constrained('personnels')->onDelete('cascade');
    $table->foreignId('prime_id')->constrained('primes')->onDelete('cascade');

    // Évaluation déplacement
    $table->string('resultat_deplacement'); // bon / mauvais / moyen
    $table->tinyInteger('organisation');
    $table->tinyInteger('respect_horaires');
    $table->tinyInteger('gestion_couts');
    $table->text('commentaire_deplacement')->nullable();

    // Évaluation personnel
    $table->tinyInteger('ponctualite');
    $table->tinyInteger('communication');
    $table->tinyInteger('professionnalisme');
    $table->tinyInteger('autonomie');
    $table->text('commentaire_personnel')->nullable();

    // Fichier justificatif
    $table->string('justificatif_path')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
