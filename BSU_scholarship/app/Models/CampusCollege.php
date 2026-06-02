<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampusCollege extends Model
{
    use HasFactory;

    protected $table = 'campus_college';

    protected $fillable = ['campus_id', 'college_id'];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }
}
