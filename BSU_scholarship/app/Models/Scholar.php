<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Scholar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'scholarship_id',
        'application_id',
        'type',
        'grant_count',
        'total_grant_received',
        'scholarship_start_date',
        'scholarship_end_date',
        'status',
        'notes',
        'grant_history'
    ];

    protected $casts = [
        'scholarship_start_date' => 'date',
        'scholarship_end_date' => 'date',
        'total_grant_received' => 'decimal:2',
        'grant_history' => 'array'
    ];

    /**
     * Get the user (student) that owns the scholar record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the scholarship that the scholar is enrolled in
     */
    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    /**
     * Get the original application that led to this scholar record
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Check if the scholar is new (hasn't received any grants yet)
     */
    public function isNew(): bool
    {
        return $this->type === 'new' && $this->grant_count === 0;
    }

    /**
     * Check if the scholar is old (has received grants)
     */
    public function isOld(): bool
    {
        return $this->type === 'old' && $this->grant_count > 0;
    }

    /**
     * Check if the scholar is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the scholar is eligible for renewal
     */
    public function isEligibleForRenewal(): bool
    {
        return $this->isActive() && 
               $this->scholarship->renewal_allowed && 
               $this->scholarship_end_date && 
               $this->scholarship_end_date->isFuture();
    }

    /**
     * Get the scholar's full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->user->name;
    }

    /**
     * Get the scholar's campus
     */
    public function getCampusAttribute(): string
    {
        return $this->user->campus->name;
    }

    /**
     * Get the scholar's program
     */
    public function getProgramAttribute(): string
    {
        return $this->user->form->program ?? 'N/A';
    }

    /**
     * Get the scholar's year level
     */
    public function getYearLevelAttribute(): string
    {
        return $this->user->form->year_level ?? 'N/A';
    }

    /**
     * Get the scholar's GWA
     */
    public function getGwaAttribute(): float
    {
        return $this->user->form->previous_gwa ?? 0.0;
    }

    /**
     * Add a grant to the scholar's history
     */
    public function addGrant(float $amount, string $description = null): void
    {
        $this->grant_count++;
        $this->total_grant_received += $amount;
        
        // Update type to 'old' if this is the first grant
        if ($this->type === 'new' && $this->grant_count === 1) {
            $this->type = 'old';
        }
        
        // Add to grant history
        $grantHistory = $this->grant_history ?? [];
        $grantHistory[] = [
            'date' => now()->toDateString(),
            'amount' => $amount,
            'description' => $description,
            'grant_number' => $this->grant_count
        ];
        
        $this->grant_history = $grantHistory;
        $this->save();
    }

    /**
     * Get the scholar's scholarship duration in months
     */
    public function getDurationInMonths(): int
    {
        if (!$this->scholarship_end_date) {
            return $this->scholarship_start_date->diffInMonths(now());
        }
        
        return $this->scholarship_start_date->diffInMonths($this->scholarship_end_date);
    }

    /**
     * Get the scholar's scholarship duration in days
     */
    public function getDurationInDays(): int
    {
        if (!$this->scholarship_end_date) {
            return $this->scholarship_start_date->diffInDays(now());
        }
        
        return $this->scholarship_start_date->diffInDays($this->scholarship_end_date);
    }

    /**
     * Scope for new scholars
     */
    public function scopeNew($query)
    {
        return $query->where('type', 'new');
    }

    /**
     * Scope for old scholars
     */
    public function scopeOld($query)
    {
        return $query->where('type', 'old');
    }

    /**
     * Scope for active scholars
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for scholars by campus
     */
    public function scopeByCampus($query, $campusId)
    {
        return $query->whereHas('user', function($q) use ($campusId) {
            $q->where('campus_id', $campusId);
        });
    }

    /**
     * Scope for scholars by scholarship
     */
    public function scopeByScholarship($query, $scholarshipId)
    {
        return $query->where('scholarship_id', $scholarshipId);
    }
}
