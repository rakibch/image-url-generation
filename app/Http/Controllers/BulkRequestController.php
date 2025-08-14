<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImageJob;
use App\Models\BulkRequest;
use App\Models\ImageTask;
use Illuminate\Http\Request;

class BulkRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['urls' => 'required|string']);

        $user = $request->user();
        $plan = $user->plan ?? 'free';
        $cfg  = config('thumbnails.plans')[$plan];

        // parse + clean
        $urls = collect(preg_split('/\r\n|\n|\r/', trim($request->input('urls'))))
            ->map(fn($u) => trim($u))
            ->filter()
            ->unique()
            ->values();

        if ($urls->isEmpty()) {
            return response()->json(['message' => 'No URLs provided'], 422);
        }

        if ($urls->count() > $cfg['quota']) {
            return response()->json(['message' => "Max {$cfg['quota']} URLs allowed for {$plan}."], 422);
        }

        $bulk = BulkRequest::create([
            'user_id' => $user->id,
            'plan'    => $plan,
            'total'   => $urls->count(),
            'status'  => 'processing',
        ]);

        $queue = $cfg['queue'];

        $urls->each(function ($url) use ($bulk, $queue) {
            $task = ImageTask::create([
                'bulk_request_id' => $bulk->id,
                'image_url'       => $url,
            ]);

            ProcessImageJob::dispatch($task->id)->onQueue($queue);
        });

        return response()->json([
            'bulk_request_id' => $bulk->id,
            'queued'          => $urls->count(),
            'plan'            => $plan,
        ], 201);
    }
}
