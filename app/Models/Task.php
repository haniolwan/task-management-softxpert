<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    protected $fillable = [
        'title',
        'description',
        'assignee_id',
        'due_date',
        'status',
    ];


    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'tasks_dependencies', 'task_id', 'depends_on_task_id')->withPivot([]);
    }

}
