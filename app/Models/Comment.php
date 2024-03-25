<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'content',
        'user_id',
    ];

    // Relación inversa: muchos comentarios pertenecen a una tarea
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
