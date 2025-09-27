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
        Schema::table('population_cells', function (Blueprint $table) {
            $table->foreignId('grid_cell_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('population_cells', function (Blueprint $table) {
            $table->dropForeign(['grid_cell_id']);
            $table->dropColumn('grid_cell_id');
        });
    }
};
