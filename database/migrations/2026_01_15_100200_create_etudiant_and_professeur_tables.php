<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etudiant', function (Blueprint $table) {
            $table->id('id_etudiant');
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('email')->unique();
            $table->integer('promo');
            $table->unsignedBigInteger('id_formation');

            $table->foreign('id_formation')
                ->references('id_formation')
                ->on('formation')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('id_formation');
        });

        Schema::create('professeur', function (Blueprint $table) {
            $table->id('id_prof');
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('email')->unique();
            $table->string('grade')->nullable();
            $table->unsignedBigInteger('id_dept');
            $table->integer('nb_surveillances_periode')->default(0);

            $table->foreign('id_dept')
                ->references('id_dept')
                ->on('departement')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('id_dept');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professeur');
        Schema::dropIfExists('etudiant');
    }
};

