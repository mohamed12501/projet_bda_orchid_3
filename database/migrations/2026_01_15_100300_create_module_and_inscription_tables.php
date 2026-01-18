<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module', function (Blueprint $table) {
            $table->id('id_module');
            $table->string('nom');
            $table->integer('credits')->nullable();
            $table->unsignedBigInteger('id_formation');
            $table->unsignedBigInteger('pre_requis_id')->nullable();
            $table->boolean('necessite_equipement')->default(false);

            $table->foreign('id_formation')
                ->references('id_formation')
                ->on('formation')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('pre_requis_id')
                ->references('id_module')
                ->on('module')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index('id_formation');
            $table->index('pre_requis_id');
        });

        Schema::create('inscription', function (Blueprint $table) {
            $table->unsignedBigInteger('id_etudiant');
            $table->unsignedBigInteger('id_module');
            $table->decimal('note', 5, 2)->nullable();

            $table->primary(['id_etudiant', 'id_module']);

            $table->foreign('id_etudiant')
                ->references('id_etudiant')
                ->on('etudiant')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('id_module')
                ->references('id_module')
                ->on('module')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('id_module');
            $table->index('id_etudiant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscription');
        Schema::dropIfExists('module');
    }
};

