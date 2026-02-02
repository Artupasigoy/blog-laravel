<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "posts";
    protected $fillable = [
        "user_id",
        "title",
        "slug",
        "category_id",
        "content",
        "thumbnail",
        "views",
        "is_featured",
        "enable_comment",
        "status",
    ];

    // Cast atribut ke tipe data primitif
    protected $casts = [
        'is_featured' => 'boolean', // Artikel unggulan?
        'enable_comment' => 'boolean', // Komentar aktif?
        'status' => 'boolean', // Status publikasi (Aktif/Draft)
    ];

    // Relasi ke Kategori (Artikel milik satu kategori)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke User (Penulis artikel)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Menghitung estimasi waktu baca (berdasarkan jumlah kata)
    public function readTime()
    {
        $minutesToRead = round(Str::wordCount(static::find($this->id)->content) / 200);
        if ($minutesToRead < 1) {
            return "Less than a minute";
        }
        return $minutesToRead . " Mins Read";
    }

    // Relasi ke Tag (Artikel punya banyak tag)
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    // Relasi ke Komentar (Artikel punya banyak komentar)
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy("created_at", "ASC");
    }
}
