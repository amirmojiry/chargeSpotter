<?php

namespace App\Console\Commands;

use App\Services\ImportService;
use Illuminate\Console\Command;

class ImportPoi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chargespotter:import-poi {file : Path to POI CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import POI locations from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("Importing POI locations from: {$file}");

        $importService = new ImportService();
        $count = $importService->importPoiCsv($file);

        $this->info("Imported {$count} POI locations");
        return 0;
    }
}
