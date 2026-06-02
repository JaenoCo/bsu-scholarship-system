<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSubmittedDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'scholarship_id',
        'document_category',
        'document_name',
        'file_path',
        'original_filename',
        'file_type',
        'file_size',
        'is_mandatory',
        'description',
        'evaluation_status',
        'evaluation_notes',
        'evaluated_by',
        'evaluated_at',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'file_size' => 'integer',
        'evaluated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    // Scopes
    public function scopeSfaoRequired($query)
    {
        return $query->where('document_category', 'sfao_required');
    }

    public function scopeScholarshipRequired($query)
    {
        return $query->where('document_category', 'scholarship_required');
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_mandatory', false);
    }

    public function scopeByUserAndScholarship($query, $userId, $scholarshipId)
    {
        return $query->where('user_id', $userId)->where('scholarship_id', $scholarshipId);
    }

    public function scopePending($query)
    {
        return $query->where('evaluation_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('evaluation_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('evaluation_status', 'rejected');
    }

    public function scopeEvaluated($query)
    {
        return $query->whereIn('evaluation_status', ['approved', 'rejected']);
    }

    // Helper methods
    public function getDocumentCategoryDisplayName()
    {
        return match($this->document_category) {
            'sfao_required' => 'SFAO Required Document',
            'scholarship_required' => 'Scholarship Required Document',
            default => 'Unknown'
        };
    }

    public function getFileSizeFormatted()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileTypeDisplayName()
    {
        return match(strtolower($this->file_type)) {
            'pdf' => 'PDF Document',
            'jpg', 'jpeg' => 'JPEG Image',
            'png' => 'PNG Image',
            'gif' => 'GIF Image',
            'docx' => 'Word Document',
            default => strtoupper($this->file_type) . ' File'
        };
    }

    public function getViewUrl()
    {
        // Use custom viewer route for all files to support favicon and better UX
        return route('document.view', ['id' => $this->id]);
    }

    public function getMandatoryStatusDisplayName()
    {
        return $this->is_mandatory ? 'Required' : 'Optional';
    }

    public function getEvaluationStatusDisplayName()
    {
        return match($this->evaluation_status) {
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown'
        };
    }

    public function getEvaluationStatusBadgeColor()
    {
        return match($this->evaluation_status) {
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
        };
    }

    public function isEvaluated()
    {
        return in_array($this->evaluation_status, ['approved', 'rejected']);
    }

    public function isPending()
    {
        return $this->evaluation_status === 'pending';
    }

    public function isApproved()
    {
        return $this->evaluation_status === 'approved';
    }

    public function isRejected()
    {
        return $this->evaluation_status === 'rejected';
    }
}