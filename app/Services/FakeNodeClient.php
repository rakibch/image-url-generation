<?php

namespace App\Services;

class FakeNodeClient
{
    public function process(string $imageUrl): bool
    {
        $cfg = config('thumbnails');

        // simulate a delay
        usleep(rand($cfg['min_delay_ms'], $cfg['max_delay_ms']) * 1000);

        // simulate success/failure
        return mt_rand() / mt_getrandmax() <= $cfg['success_rate'];
    }
}
