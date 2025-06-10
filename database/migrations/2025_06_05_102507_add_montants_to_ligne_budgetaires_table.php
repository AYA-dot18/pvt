<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ligne_budgetaires', function (Blueprint $table) {
            $table->decimal('montant_initial', 15, 2)->default(0);
            $table->decimal('montant_restant', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('ligne_budgetaires', function (Blueprint $table) {
            $table->dropColumn(['montant_initial', 'montant_restant']);
        });
    }
};
