<?php

namespace App\Console\Commands;

use App\Services\ScoringService;
use Illuminate\Console\Command;

class NormalizeLayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chargespotter:normalize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize layer values to 0..1 range';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Normalizing layer values...");

        $scoringService = new ScoringService();
        $scoringService->normalizeLayers();

        $this->info("Layer values normalized successfully");
        return 0;
    }
}
