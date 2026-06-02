<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = ['campus_college_id', 'name', 'short_name'];

    public function campusCollege()
    {
        return $this->belongsTo(CampusCollege::class);
    }

    public function tracks()
    {
        return $this->hasMany(ProgramTrack::class);
    }
}
