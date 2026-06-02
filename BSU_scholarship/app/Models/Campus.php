<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'parent_campus_id',
        'has_sfao_admin',
    ];

    protected $casts = [
        'has_sfao_admin' => 'boolean',
    ];

    // ✅ Each campus can have many users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // ✅ Each campus can have many reports
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // ✅ Parent campus relationship (for constituent campuses)
    public function parentCampus()
    {
        return $this->belongsTo(Campus::class, 'parent_campus_id');
    }

    // ✅ Extension campuses relationship
    public function extensionCampuses()
    {
        return $this->hasMany(Campus::class, 'parent_campus_id');
    }

    // ✅ Get all campuses under this constituent campus (including itself and extensions)
    public function getAllCampusesUnder()
    {
        $campuses = collect([$this]);
        
        foreach ($this->extensionCampuses as $extension) {
            $campuses->push($extension);
        }
        
        return $campuses;
    }

    // ✅ Get all users from this campus and its extensions
    public function getAllUsersUnder()
    {
        $campusIds = $this->getAllCampusesUnder()->pluck('id');
        return User::whereIn('campus_id', $campusIds);
    }

    // ✅ Scope for constituent campuses only
    public function scopeConstituent($query)
    {
        return $query->where('type', 'constituent');
    }

    // ✅ Scope for extension campuses only
    public function scopeExtension($query)
    {
        return $query->where('type', 'extension');
    }

    // ✅ Scope for campuses with SFAO admin
    public function scopeWithSfaoAdmin($query)
    {
        return $query->where('has_sfao_admin', true);
    }

    public function colleges()
    {
        return $this->belongsToMany(College::class, 'campus_college');
    }

    public function scholars()
    {
        return $this->hasManyThrough(Scholar::class, User::class);
    }

    public function scholarships()
    {
        return $this->belongsToMany(Scholarship::class, 'campus_scholarship');
    }
}
