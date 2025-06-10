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

        Schema::create('tgrs', function (Blueprint $table) {
            $table->id();
            $table->float('montant');
            $table->enum('type', ["ik","prime"]);
            $table->text('tgr_path');
            $table->string('statut');
            $table->foreignId('ligne_budgetaire_id')->constrained();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tgrs');
    }
};
