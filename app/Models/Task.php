<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'todo_id', 'task', 'finished'
    ];

    protected $casts = [
        'finished' => 'bool'
    ];

    public $timestamps = true;

    public function todo() {
        return $this->belongsTo(Todo::class);
    }
}
