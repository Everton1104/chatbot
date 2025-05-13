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
        Schema::create('conversas', function (Blueprint $table) {
            $table->id();
            $table->string('numero');
            $table->string('nome')->default('Sem_Nome');
            $table->integer('status')->default(0);
            $table->timestamps();
        });
        Schema::create('mensagens', function (Blueprint $table) {
            $table->id();
            $table->string('numero_id');
            $table->longText('msg');
            $table->integer('tipo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversas');
        Schema::dropIfExists('mensagens');
    }
};
