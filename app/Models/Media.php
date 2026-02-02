<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = "media";

    protected $fillable = [
        "user_id", // Pengupload
        "file_name", // Nama file fisik
    ];

    /**
     * Get the file size in human readable format.
     */
    public function getFileSizeAttribute()
    {
        // Try public path first
        $path = public_path('uploads/media/' . $this->file_name);
        if (!\Illuminate\Support\Facades\File::exists($path)) {
            // Try trash path if not found in public
            $path = storage_path("app/trash/" . $this->file_name);
        }

        if (\Illuminate\Support\Facades\File::exists($path)) {
            $bytes = \Illuminate\Support\Facades\File::size($path);
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            for ($i = 0; $bytes > 1024; $i++) {
                $bytes /= 1024;
            }
            return round($bytes, 2) . ' ' . $units[$i];
        }
        return 'Unknown';
    }

    /**
     * Get the file extension.
     */
    public function getFileExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    protected $appends = ['file_size', 'file_extension'];
}
