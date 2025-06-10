<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('logs', function (Blueprint $table) {
        $table->id();
        $table->timestamp('date')->useCurrent();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->unsignedBigInteger('personnel_id')->nullable();
        $table->string('action');
        $table->json('old_values')->nullable();
        $table->json('new_values')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
