<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universite', function (Blueprint $table) {
            $table->id('id_univ');
            $table->string('nom');
            $table->string('ville');
        });

        Schema::create('faculte', function (Blueprint $table) {
            $table->id('id_fac');
            $table->string('nom');
            $table->unsignedBigInteger('id_univ');

            $table->foreign('id_univ')
                ->references('id_univ')
                ->on('universite')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculte');
        Schema::dropIfExists('universite');
    }
};

