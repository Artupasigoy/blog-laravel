<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    use HasFactory;

    protected $table = "social_media";
    protected $fillable = [
        "title", // Nama Platform (misal: Facebook)
        "icon", // Kelas Icon FontAwesome
        "link", // URL Profil
        "color", // Warna Hex Brand
        "status", // Tampilkan di footer?
    ];

    protected $casts = [
        "status" => "boolean",
    ];
}
