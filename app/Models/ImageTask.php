<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageTask extends Model
{
    protected $fillable = ['bulk_request_id','image_url','status','processed_at','error'];

    public function bulkRequest() {
        return $this->belongsTo(BulkRequest::class);
    }
}
