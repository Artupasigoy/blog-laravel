<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = "tags";
    protected $fillable = [
        "name",
    ];

    // Relasi ke Post (Tag dimiliki banyak artikel)
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
