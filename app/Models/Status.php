<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $table = 'status';

    public $timestamps = false;

    protected $fillable = [
        'status',
    ];

    // Relación con las tasks
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
