<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $table = "site_settings";

    protected $fillable = [
        "site_title", // Judul Website
        "tagline", // Slogan
        "description", // Deskripsi SEO
        "logo_dark", // Logo Mode Gelap
        "logo_light", // Logo Mode Terang
        "copyright_text", // Teks Footer
        "enable_registration", // Izinkan Registrasi User?
    ];

    protected $casts = [
        "enable_registration" => "boolean",
    ];
}
