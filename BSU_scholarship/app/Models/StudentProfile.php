<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'street', 
        'barangay', 
        'town', 
        'province', 
        'zip_code',
        'gwa',
        'units_enrolled',
        'father_name',
        'mother_name',
        'father_occupation',
        'mother_occupation',
        'annual_gross_income'
    ];
}
