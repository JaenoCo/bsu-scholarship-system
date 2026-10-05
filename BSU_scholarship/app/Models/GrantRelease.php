<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrantRelease extends Model
{
    protected $fillable = [
        'application_id',
        'scholar_id',
        'user_id',
        'scholarship_id',
        'released_by',
        'grant_number',
        'amount',
        'tracking_number',
        'qr_code',
        'status',
        'released_at',
        'email_sent_at',
        'email_error',
        'notification_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'released_at' => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scholar(): BelongsTo
    {
        return $this->belongsTo(Scholar::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function releasingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
