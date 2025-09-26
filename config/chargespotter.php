<?php

return [
    'default_weights' => [
        'population' => 0.4,
        'poi'        => 0.3,
        'parking'    => 0.2,
        'traffic'    => 0.1,
    ],
    'grid' => [
        'cell_size_m' => 500, // 250..500 recommended
    ],
    'export' => [
        'max_candidates' => 50,
    ],
];
