<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RejectedApplicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'scholarship_id',
        'application_id',
        'rejected_by',
        'rejected_by_user_id',
        'rejection_reason',
        'remarks',
        'rejection_data',
        'rejected_at'
    ];

    protected $casts = [
        'rejection_data' => 'array',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user (student) who was rejected
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the scholarship they were rejected from
     */
    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    /**
     * Get the original application
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the user who rejected the application (SFAO or Central admin)
     */
    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    /**
     * Get the rejection reason display text
     */
    public function getRejectionReasonDisplayAttribute(): string
    {
        return $this->rejection_reason ?: 'No reason provided';
    }

    /**
     * Get the rejected by display text
     */
    public function getRejectedByDisplayAttribute(): string
    {
        return match($this->rejected_by) {
            'sfao' => 'SFAO',
            'central' => 'Central Administration',
            default => 'Unknown'
        };
    }

    /**
     * Get formatted rejection date
     */
    public function getFormattedRejectedAtAttribute(): string
    {
        return $this->rejected_at ? $this->rejected_at->format('M d, Y h:i A') : 'Unknown';
    }

    /**
     * Get time since rejection
     */
    public function getTimeSinceRejectionAttribute(): string
    {
        return $this->rejected_at ? $this->rejected_at->diffForHumans() : 'Unknown';
    }

    /**
     * Check if rejection is recent (within last 7 days)
     */
    public function isRecentRejection(): bool
    {
        return $this->rejected_at && $this->rejected_at->isAfter(now()->subDays(7));
    }

    /**
     * Scope for SFAO rejections
     */
    public function scopeSfaoRejections($query)
    {
        return $query->where('rejected_by', 'sfao');
    }

    /**
     * Scope for Central rejections
     */
    public function scopeCentralRejections($query)
    {
        return $query->where('rejected_by', 'central');
    }

    /**
     * Scope for recent rejections
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('rejected_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for rejections by scholarship
     */
    public function scopeByScholarship($query, $scholarshipId)
    {
        return $query->where('scholarship_id', $scholarshipId);
    }

    /**
     * Scope for rejections by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
