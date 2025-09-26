<?php

namespace App\Console\Commands;

use App\Services\ScoringService;
use Illuminate\Console\Command;

class ComputeScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chargespotter:compute 
                            {--weights=0.4,0.3,0.2,0.1 : Weights as population,poi,parking,traffic}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute total scores with given weights';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $weightsString = $this->option('weights');
        $weightValues = array_map('floatval', explode(',', $weightsString));

        if (count($weightValues) !== 4) {
            $this->error('Weights must have 4 values: population,poi,parking,traffic');
            return 1;
        }

        $weights = [
            'population' => $weightValues[0],
            'poi' => $weightValues[1],
            'parking' => $weightValues[2],
            'traffic' => $weightValues[3],
        ];

        $this->info("Computing scores with weights: " . implode(',', $weightValues));

        $scoringService = new ScoringService();
        $scoringService->computeTotals($weights);

        $this->info("Scores computed successfully");
        return 0;
    }
}
