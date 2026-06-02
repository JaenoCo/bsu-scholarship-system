<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'description',
    ];

    public function campuses()
    {
        return $this->belongsToMany(Campus::class, 'campus_department');
    }
}
