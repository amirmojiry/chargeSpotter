<?php

namespace App\Console\Commands;

use App\Services\ImportService;
use Illuminate\Console\Command;

class ImportPopulation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chargespotter:import-population {file : Path to population CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import population data from CSV file';

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

        $this->info("Importing population data from: {$file}");

        $importService = new ImportService();
        $count = $importService->importPopulationCsv($file);

        $this->info("Imported {$count} population cells");
        return 0;
    }
}
