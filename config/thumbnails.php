<?php

return [
    'plans' => [
        'free'       => ['quota' => 50,  'queue' => 'free',       'priority' => 1],
        'pro'        => ['quota' => 100, 'queue' => 'pro',        'priority' => 2],
        'enterprise' => ['quota' => 200, 'queue' => 'enterprise', 'priority' => 3],
    ],
    'success_rate' => 0.85,   // simulation: 85% success
    'min_delay_ms' => 800,    // simulation min delay
    'max_delay_ms' => 2500,   // simulation max delay
];
