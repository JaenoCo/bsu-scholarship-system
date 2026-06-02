<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipRequiredCondition extends Model
{
    use HasFactory;

    protected $table = 'scholarship_required_conditions';

    protected $fillable = [
        'scholarship_id',
        'name',
        'value',
        'is_mandatory',
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

    // Helper methods
    public function getConditionDisplayName()
    {
        return match($this->name) {
            'gwa' => 'GWA Requirement',
            'year_level' => 'Year Level',
            'income' => 'Income Bracket',
            'disability' => 'Disability Status',
            'program' => 'Program',
            'campus' => 'Campus',
            default => ucfirst(str_replace('_', ' ', $this->name))
        };
    }

    public function getValueDisplayName()
    {
        if ($this->name === 'gwa') {
            return 'GWA ' . $this->value . ' or better';
        }
        
        if ($this->name === 'year_level') {
            return $this->value . ' or higher';
        }
        
        if ($this->name === 'income') {
            return '₱' . number_format($this->value) . ' or below';
        }
        
        return $this->value;
    }
}
