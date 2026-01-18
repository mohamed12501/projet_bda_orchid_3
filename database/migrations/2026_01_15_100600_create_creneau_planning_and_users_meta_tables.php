<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creneau', function (Blueprint $table) {
            $table->bigIncrements('id_creneau');
            $table->date('date');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index('date');
            $table->index(['date', 'heure_debut']);
        });

        Schema::create('planning_runs', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->enum('scope', ['global', 'departement', 'formation']);
            $table->unsignedBigInteger('dept_id')->nullable();
            $table->unsignedBigInteger('formation_id')->nullable();
            $table->enum('status', ['pending', 'running', 'done', 'failed'])->default('pending');
            $table->boolean('published')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->json('metrics');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->enum('status_admin', ['draft', 'submitted'])->default('draft');
            $table->enum('status_doyen', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('rejected_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('dept_id')
                ->references('id_dept')
                ->on('departement')
                ->nullOnDelete();

            $table->foreign('formation_id')
                ->references('id_formation')
                ->on('formation')
                ->nullOnDelete();
        });

        Schema::create('planning_items', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('run_id', 36)->nullable();
            $table->unsignedBigInteger('module_id')->nullable();
            $table->unsignedBigInteger('salle_id')->nullable();
            $table->unsignedBigInteger('creneau_id')->nullable();
            $table->integer('expected_students')->nullable();
            $table->text('notes')->nullable();
            $table->json('surveillants')->default(DB::raw('(JSON_ARRAY())'));

            $table->foreign('run_id')
                ->references('id')
                ->on('planning_runs')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('module_id')
                ->references('id_module')
                ->on('module')
                ->nullOnDelete();

            $table->foreign('salle_id')
                ->references('id_salle')
                ->on('salle')
                ->nullOnDelete();

            $table->foreign('creneau_id')
                ->references('id_creneau')
                ->on('creneau')
                ->nullOnDelete();

            $table->unique(['salle_id', 'creneau_id', 'run_id'], 'planning_items_unique_room_slot_run');

            $table->index('run_id');
            $table->index('creneau_id');
            $table->index('module_id');
            $table->index('salle_id');
        });

        Schema::create('users_meta', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->enum('role', ['admin_examens', 'chef_dept', 'doyen', 'prof', 'etudiant']);
            $table->unsignedBigInteger('dept_id')->nullable();
            $table->unsignedBigInteger('formation_id')->nullable();
            $table->unsignedBigInteger('id_prof')->nullable();
            $table->unsignedBigInteger('id_etudiant')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('dept_id')
                ->references('id_dept')
                ->on('departement')
                ->nullOnDelete();

            $table->foreign('formation_id')
                ->references('id_formation')
                ->on('formation')
                ->nullOnDelete();

            $table->foreign('id_prof')
                ->references('id_prof')
                ->on('professeur')
                ->nullOnDelete();

            $table->foreign('id_etudiant')
                ->references('id_etudiant')
                ->on('etudiant')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_meta');
        Schema::dropIfExists('planning_items');
        Schema::dropIfExists('planning_runs');
        Schema::dropIfExists('creneau');
    }
};

