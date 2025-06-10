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
        Schema::create('group_trip_user', function (Blueprint $table) {
        $table->id();
        $table->foreignId('group_trip_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('role')->default('participant'); // ex: chef de mission
        $table->integer('distance_parcourue')->nullable(); // en km
        $table->decimal('prime_calculee', 10, 2)->nullable(); // en euros par ex
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_trip_user');
    }
};
