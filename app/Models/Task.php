<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'is_completed',
        'completed_at',
        'priority',
        'project_id',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function markAsCompleted()
    {
        $this->is_completed = true;
        $this->completed_at = now();
        $this->save();
    }

    public function markAsIncomplete()
    {
        $this->is_completed = false;
        $this->completed_at = null;
        $this->save();
    }
}
