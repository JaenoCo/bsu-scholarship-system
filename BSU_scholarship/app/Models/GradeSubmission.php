<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeSubmission extends Model
{
    use HasFactory;

    protected $table = 'submissions';

    protected $fillable = [
        'user_id',
        'school_year',
        'semester',
        'file_path',
        'status',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subjects()
    {
        return $this->hasMany(SubmissionSubject::class, 'submission_id');
    }
}
