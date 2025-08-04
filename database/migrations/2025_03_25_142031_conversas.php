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
            $table->id()->startingValue(50); // a partir de 50 pois conflita com o id da tabela departamentos
            $table->string('numero')->nullable();
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
        Schema::create('users_departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('descDepartamento')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->integer('departamento_id')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversas');
        Schema::dropIfExists('mensagens');
        Schema::dropIfExists('users_departamentos');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('departamento_id');
        });
    }
};
