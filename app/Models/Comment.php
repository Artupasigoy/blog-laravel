<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "comments";

    protected $fillable = [
        "message",
        "post_id",
        "parent_id",
        "user_id",
        "name",
        "email",
        "status",
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Relasi ke User (Penulis komentar)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Post (Komentar pada artikel apa)
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Relasi ke Balasan (Recursive relationship)
    public function replies()
    {
        return $this->hasMany($this, "parent_id")->orderBy("created_at", "ASC");
    }
}
