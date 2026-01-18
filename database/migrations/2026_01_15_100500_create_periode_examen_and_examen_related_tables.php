<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_examen', function (Blueprint $table) {
            $table->id('id_periode');
            $table->string('nom');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->enum('type', ['session1', 'session2', 'rattrapage'])->nullable();
        });

        Schema::create('examen', function (Blueprint $table) {
            $table->id('id_examen');
            $table->unsignedBigInteger('id_module');
            $table->unsignedBigInteger('id_periode');
            $table->date('date_examen');
            $table->time('heure_debut');
            $table->integer('duree_minutes')->nullable();
            $table->enum('statut', ['planifie', 'confirme', 'annule'])->default('planifie');

            $table->foreign('id_module')
                ->references('id_module')
                ->on('module')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_periode')
                ->references('id_periode')
                ->on('periode_examen')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('id_periode');
            $table->index('date_examen');
        });

        Schema::create('session_examen', function (Blueprint $table) {
            $table->id('id_session');
            $table->unsignedBigInteger('id_examen');
            $table->unsignedBigInteger('id_salle');
            $table->integer('nb_places_allouees')->nullable();

            $table->foreign('id_examen')
                ->references('id_examen')
                ->on('examen')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_salle')
                ->references('id_salle')
                ->on('salle')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('surveillance', function (Blueprint $table) {
            $table->unsignedBigInteger('id_examen');
            $table->unsignedBigInteger('id_prof');
            $table->enum('role', ['responsable', 'surveillant'])->default('surveillant');

            $table->primary(['id_examen', 'id_prof']);

            $table->foreign('id_examen')
                ->references('id_examen')
                ->on('examen')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_prof')
                ->references('id_prof')
                ->on('professeur')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveillance');
        Schema::dropIfExists('session_examen');
        Schema::dropIfExists('examen');
        Schema::dropIfExists('periode_examen');
    }
};

