<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'birthdate',
        'sex',
        'email',
        'contact_number',
        'password',
        'role',
        'profile_picture',
        'campus_id',
        'sr_code',
        'education_level',
        'college',
        'program',
        'track',
        'year_level',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthdate' => 'date',
    ];

    // Relationships

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function appliedScholarships()
    {
        return $this->belongsToMany(Scholarship::class, 'applications')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function form()
    {
        return $this->hasOne(Form::class, 'user_id');
    }

    public function invitation()
    {
        return $this->hasOne(Invitation::class, 'email', 'email');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function scholars()
    {
        return $this->hasMany(Scholar::class);
    }
    public function documents()
    {
        return $this->hasMany(StudentSubmittedDocument::class, 'user_id');
    }
}
