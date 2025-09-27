<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add the column as nullable
        Schema::table('grid_cells', function (Blueprint $table) {
            $table->unsignedBigInteger('region_id')->nullable();
        });

        // Create a default region for existing grid cells
        $defaultRegionId = DB::table('regions')->insertGetId([
            'name' => 'Default Region',
            'description' => 'Default region for existing grid cells',
            'area_json' => json_encode([
                ['lat' => 0, 'lng' => 0],
                ['lat' => 0, 'lng' => 1],
                ['lat' => 1, 'lng' => 1],
                ['lat' => 1, 'lng' => 0],
                ['lat' => 0, 'lng' => 0]
            ]),
            'center_lat' => 0.5,
            'center_lng' => 0.5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update existing grid cells to reference the default region
        DB::table('grid_cells')->update(['region_id' => $defaultRegionId]);

        // Now make the column not nullable and add the foreign key constraint
        Schema::table('grid_cells', function (Blueprint $table) {
            $table->unsignedBigInteger('region_id')->nullable(false)->change();
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grid_cells', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');
        });

        // Remove the default region
        DB::table('regions')->where('name', 'Default Region')->delete();
    }
};
