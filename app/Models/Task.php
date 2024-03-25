<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attachment;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'completed_at',
        'status_id',
        'user_id',
    ];

    protected $dates = [
        'created_at',
        'completed_at',
    ];

    // Relación con status
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    // Relación con el empleado asignado
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }

    // Relación con los comentarios de la tarea
    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    // Relación con los archivos adjuntos
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
