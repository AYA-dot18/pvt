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
        Schema::create('personnels', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 191);
            $table->string('prenom', 191);
            $table->string('num_cin', 191);
            $table->string('num_ppr', 191);
            $table->string('grade', 191);
            $table->string('echelle', 191);
            $table->string('groupe', 191);
            $table->unsignedInteger('taux_indemnite')->nullable();
            $table->float('montant_indemnite');
            $table->string('banque_rib')->nullable();
            $table->string('guichet_rib')->nullable();
            $table->string('num_compte_rib')->nullable();
            $table->string('code_rib')->nullable();
            $table->string('residence', 191);
            $table->string('suffix', 191);
            $table->boolean('statut');
            $table->float('creance')->default(0);
            $table->string('situation_familiale', 191);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnels');
    }
};
