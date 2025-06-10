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

        Schema::create('trajets', function (Blueprint $table) {
            $table->id();
            $table->string('ville', 191);
            $table->string('aller', 191);
            $table->string('retour', 191);
            $table->string('trajet', 191);
            $table->integer('km_route');
            $table->integer('km_piste')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trajets');
    }
};
