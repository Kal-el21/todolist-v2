<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function getCompletedTasksCountAttribute()
    {
        return $this->tasks()->where('is_completed', true)->count();
    }

    public function getPendingTasksCountAttribute()
    {
        return $this->tasks()->where('is_completed', false)->count();
    }
}
