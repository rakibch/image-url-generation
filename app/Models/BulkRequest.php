<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkRequest extends Model
{
    protected $fillable = ['user_id','plan','total','status'];

    public function tasks() {
        return $this->hasMany(ImageTask::class);
    }
}
