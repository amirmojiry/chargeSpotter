<?php

namespace App\Console\Commands;

use App\Services\ImportService;
use Illuminate\Console\Command;

class ImportParking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chargespotter:import-parking {file : Path to parking CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import parking locations from CSV file';

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

        $this->info("Importing parking locations from: {$file}");

        $importService = new ImportService();
        $count = $importService->importParkingCsv($file);

        $this->info("Imported {$count} parking locations");
        return 0;
    }
}
