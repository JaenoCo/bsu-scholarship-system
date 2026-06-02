<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_name',
        'form_type',
        'description',
        'file_path',
        'file_type',
        'campus_id',
        'uploaded_by',
        'download_count',
    ];

    /**
     * Get the campus this form belongs to
     */
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Get the user who uploaded this form
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Increment download count
     */
    public function incrementDownloads()
    {
        $this->increment('download_count');
    }

    /**
     * Get file extension
     */
    public function getFileExtension()
    {
        return pathinfo($this->file_path, PATHINFO_EXTENSION);
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSize()
    {
        $path = storage_path('app/' . $this->file_path);
        if (file_exists($path)) {
            $bytes = filesize($path);
            $units = ['B', 'KB', 'MB', 'GB'];
            $i = 0;
            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }
            return round($bytes, 2) . ' ' . $units[$i];
        }
        return 'Unknown';
    }
}
