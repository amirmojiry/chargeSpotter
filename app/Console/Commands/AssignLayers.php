<?php

namespace App\Console\Commands;

use App\Services\ImportService;
use Illuminate\Console\Command;

class AssignLayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chargespotter:assign-layers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign layer values to grid cells';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Assigning layer values to grid cells...");

        $importService = new ImportService();
        $importService->assignLayerValuesToGrid();

        $this->info("Layer values assigned successfully");
        return 0;
    }
}
