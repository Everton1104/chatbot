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
            $table->integer('user_id')->default(0);
            $table->string('numero')->nullable();;
            $table->string('nome')->default('Sem_Nome');
            $table->string('foto')->default('0.jpg');
            $table->integer('status')->default(0);
            $table->timestamps();
        });
        Schema::create('mensagens', function (Blueprint $table) {
            $table->id();
            $table->integer('conversa_id_to');
            $table->integer('conversa_id_from');
            $table->longText('msg');
            $table->string('link')->nullable();
            $table->integer('tipo')->nullable();
            $table->integer('status')->default(0);
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
