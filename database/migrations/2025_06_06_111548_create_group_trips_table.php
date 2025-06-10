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
        Schema::create('group_trips', function (Blueprint $table) {
        $table->id();
        $table->string('titre');
        $table->string('lieu');
        $table->date('date_depart');
        $table->date('date_retour');
        $table->string('type_mission')->nullable(); // ex: audit, intervention...
        $table->string('moyen_transport')->nullable(); // ex: train, voiture
        $table->decimal('cout_total_estime', 10, 2)->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_trips');
    }
};
