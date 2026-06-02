<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipRequiredDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholarship_id',
        'document_name',
        'document_type',
        'is_mandatory',
        'description',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    // Relationship back to scholarship
    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    // Scopes for filtering
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_mandatory', false);
    }

    // Scopes for filtering
    public function scopeByDocumentType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    // Helper methods
    public function getDocumentTypeDisplayName()
    {
        return match($this->document_type) {
            'pdf' => 'PDF Document',
            'image' => 'Image File',
            'both' => 'PDF or Image',
            default => 'Unknown'
        };
    }

    public function getMandatoryStatusDisplayName()
    {
        return $this->is_mandatory ? 'Required' : 'Optional';
    }
}