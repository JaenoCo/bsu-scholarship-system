<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramTrack extends Model
{
    use HasFactory;

    protected $fillable = ['program_id', 'name', 'type'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
