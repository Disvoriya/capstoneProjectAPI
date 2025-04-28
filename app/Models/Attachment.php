<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'file_path',
        'original_name',
        'size',
        'file_type'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

}
