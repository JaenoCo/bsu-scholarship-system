<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'token',
        'campus_id',
        'invited_by',
        'status',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    /**
     * Generate a unique token for the invitation
     */
    public static function generateToken()
    {
        do {
            $token = \Illuminate\Support\Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Create a new invitation
     */
    public static function createInvitation($email, $name, $campusId, $invitedBy)
    {
        return self::create([
            'email' => $email,
            'name' => $name,
            'token' => self::generateToken(),
            'campus_id' => $campusId,
            'invited_by' => $invitedBy,
            'expires_at' => Carbon::now()->addDays(7), // 7 days expiry
        ]);
    }

    /**
     * Check if invitation is expired
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if invitation is valid (not expired and pending)
     */
    public function isValid()
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Accept the invitation (mark as active when email is verified)
     */
    public function accept()
    {
        $this->update([
            'status' => 'active',
            'accepted_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark invitation as deactive (if user account is deactivated)
     */
    public function markAsDeactive()
    {
        $this->update(['status' => 'deactive']);
    }

    /**
     * Mark invitation as active (reactivate user)
     */
    public function markAsActive()
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Mark invitation as expired
     */
    public function markAsExpired()
    {
        $this->update(['status' => 'deactive']);
    }

    /**
     * Get the campus for this invitation
     */
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Get the user who sent this invitation
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Scope for pending invitations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for active invitations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for deactive invitations
     */
    public function scopeDeactive($query)
    {
        return $query->where('status', 'deactive');
    }

    /**
     * Scope for removed invitations
     */
    public function scopeRemoved($query)
    {
        return $query->where('status', 'removed');
    }

    /**
     * Scope for expired invitations
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now());
    }

    /**
     * Scope for valid invitations
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
                    ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Check if invitation is active (not deactive)
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if invitation is deactive
     */
    public function isDeactive()
    {
        return $this->status === 'deactive';
    }

    /**
     * Check if invitation is removed
     */
    public function isRemoved()
    {
        return $this->status === 'removed';
    }

    /**
     * Mark invitation as removed (when user is deleted)
     */
    public function markAsRemoved()
    {
        $this->update(['status' => 'removed']);
    }
}
