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
        Schema::table('etudiant', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->nullable()->after('id_formation');
            $table->unsignedBigInteger('group_id')->nullable()->after('section_id');
            
            $table->foreign('section_id')
                ->references('id_section')
                ->on('section')
                ->nullOnDelete();
            
            $table->foreign('group_id')
                ->references('id_groupe')
                ->on('groupe')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etudiant', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropForeign(['group_id']);
            $table->dropColumn(['section_id', 'group_id']);
        });
    }
};
