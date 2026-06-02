<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SfaoRequirement extends Model
{
    protected $table = 'sfao_required_documents';

    protected $fillable = [
        'user_id',
        'scholarship_id',
        'form_137',
        'grades',
        'certificate',
        'application_form',
    ];

    // 🔗 Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
