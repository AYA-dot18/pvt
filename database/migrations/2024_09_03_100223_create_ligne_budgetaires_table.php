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

        Schema::create('ligne_budgetaires', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 191);
            $table->string('type', 191);
            $table->string('exercice', 191);
            $table->string('chapitre', 191);
            $table->string('article', 191)->nullable();
            $table->string('paragraphe', 191)->nullable();
            $table->string('ligne', 191)->nullable();
            $table->string('programme', 191)->nullable();
            $table->string('region', 191)->nullable();
            $table->string('projet', 191)->nullable();
            $table->string('ligne_budgetaire', 191)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_budgetaires');
    }
};
