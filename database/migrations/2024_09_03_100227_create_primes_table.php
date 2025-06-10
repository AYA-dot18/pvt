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

        Schema::create('primes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained();
            $table->float('montant_initial');
            $table->float('montant');
            $table->unsignedInteger('nombre_taux')->nullable();
            $table->string('changetaux',191)->nullable();
            $table->string('type', 191);
            $table->float('creance_cree')->default(0);
            $table->float('ancienne_creance')->default(0);
            $table->float('nouvelle_creance')->default(0);
            $table->boolean('ik')->default(0);
            $table->text('remarque')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('primes');
    }
};
