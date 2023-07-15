<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $fillable = [
        'name', 'description', 'due_date', 'remind_me', 'important'
    ];

    public $timestamps = true;

    protected $casts = [
        'remind_me' => 'bool',
        'important' => 'bool'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function tasks() {
        return $this->hasMany(Task::class);
    }
}
