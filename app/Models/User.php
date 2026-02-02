<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public const IS_VISITOR = 1;
    public const IS_AUTHOR = 2;
    public const IS_ADMIN = 3;

    protected $fillable = [
        'name',
        'username',
        'email',
        'profile',
        'about',
        'role',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'youtube',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'boolean',
    ];

    protected function username(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Str::lower($value)
        );
    }

    // Relasi ke model Post (Satu user bisa punya banyak post)
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // Mendapatkan URL gambar profil (thumbnail)
    public function getPictureAttribute($value)
    {
        if ($value) {
            return asset("/storage/images/user_profile/" . $value);
        }
        return asset("/storage/images/user_profile/default_profile_picture.jpg");
    }

    // Relasi ke tabel social_media melalui tabel pivot
    public function social_media()
    {
        return $this->belongsToMany(SocialMedia::class, "user_social_media");
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }
}
