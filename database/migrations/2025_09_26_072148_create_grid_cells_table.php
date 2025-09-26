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
        Schema::create('grid_cells', function (Blueprint $table) {
            $table->id();
            $table->double('lat');
            $table->double('lng');
            $table->json('bbox_json');
            $table->float('population_z')->default(0);
            $table->float('poi_z')->default(0);
            $table->float('parking_z')->default(0);
            $table->float('traffic_z')->nullable();
            $table->float('total_score_cached')->nullable();
            $table->json('details_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grid_cells');
    }
};
