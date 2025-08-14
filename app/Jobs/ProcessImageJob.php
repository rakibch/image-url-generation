<?php

namespace App\Jobs;

use App\Models\ImageTask;
use App\Services\FakeNodeClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public int $imageTaskId) {}

    public function handle(FakeNodeClient $node): void
    {
        $task = ImageTask::find($this->imageTaskId);
        if (!$task) return;

        $ok = $node->process($task->image_url);

        $task->update([
            'status'       => $ok ? 'processed' : 'failed',
            'processed_at' => now(),
            'error'        => $ok ? null : 'Simulated processing failure',
        ]);
    }
}
