<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'path',
        'task_id',
    ];

    // Relación inversa: muchos archivos adjuntos pertenecen a una tarea
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
