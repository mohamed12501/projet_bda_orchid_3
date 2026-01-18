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
        Schema::create('groupe', function (Blueprint $table) {
            $table->id('id_groupe');
            $table->string('nom');
            $table->unsignedBigInteger('id_section');
            
            $table->foreign('id_section')
                ->references('id_section')
                ->on('section')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groupe');
    }
};
