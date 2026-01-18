<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departement', function (Blueprint $table) {
            $table->id('id_dept');
            $table->string('nom');
            $table->string('lieu')->nullable();
            $table->unsignedBigInteger('id_fac');

            $table->foreign('id_fac')
                ->references('id_fac')
                ->on('faculte')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('id_fac');
        });

        Schema::create('formation', function (Blueprint $table) {
            $table->id('id_formation');
            $table->string('nom');
            $table->enum('niveau', ['L', 'M', 'D'])->nullable();
            $table->integer('nb_modules')->nullable();
            $table->unsignedBigInteger('id_dept');

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
        Schema::dropIfExists('formation');
        Schema::dropIfExists('departement');
    }
};

