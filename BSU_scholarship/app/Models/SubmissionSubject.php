<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'subject_code',
        'subject_name',
        'units',
        'grade',
    ];

    protected $casts = [
        'units' => 'decimal:2',
        'grade' => 'decimal:2',
    ];

    public function submission()
    {
        return $this->belongsTo(GradeSubmission::class, 'submission_id');
    }
}
