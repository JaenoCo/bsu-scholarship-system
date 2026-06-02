<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function campuses()
    {
        return $this->belongsToMany(Campus::class, 'campus_college');
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }
}
