<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipement', function (Blueprint $table) {
            $table->id('id_equipement');
            $table->string('nom');
            $table->text('description')->nullable();
        });

        Schema::create('salle', function (Blueprint $table) {
            $table->id('id_salle');
            $table->string('nom');
            $table->integer('capacite')->nullable();
            $table->enum('type', ['salle', 'amphi'])->nullable();
            $table->string('batiment');
            $table->integer('capacite_normale')->nullable();
            $table->integer('capacite_examen')->nullable();
        });

        Schema::create('salle_equipement', function (Blueprint $table) {
            $table->unsignedBigInteger('id_salle');
            $table->unsignedBigInteger('id_equipement');
            $table->integer('quantite')->nullable();

            $table->primary(['id_salle', 'id_equipement']);

            $table->foreign('id_salle')
                ->references('id_salle')
                ->on('salle')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_equipement')
                ->references('id_equipement')
                ->on('equipement')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salle_equipement');
        Schema::dropIfExists('salle');
        Schema::dropIfExists('equipement');
    }
};

