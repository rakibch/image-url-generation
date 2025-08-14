<?php

namespace App\Http\Controllers;

use App\Models\BulkRequest;
use Illuminate\Http\Request;

class ImageTaskController extends Controller
{
    public function index(BulkRequest $bulkRequest, Request $request)
    {
        $status = $request->query('status');
        $q = $bulkRequest->tasks()->orderByDesc('id');
        if ($status) {
            $q->where('status', $status);
        }
        return $q->paginate(20);
    }
}
