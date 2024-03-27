<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attachment;
use App\Models\User;

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

    // Oculta los campos user_id y status_id en la respuesta
    protected $hidden = ['user_id', 'status_id'];

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    // Mutador para obtener el estado con su id y nombre
    public function getStatusAttribute($value)
    {
        return [
            'id' => $this->status_id,
            'status' => $this->status()->exists() ? $this->status->status : null,
        ];
    }
}
