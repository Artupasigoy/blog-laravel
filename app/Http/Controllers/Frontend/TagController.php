<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Menampilkan daftar postingan berdasarkan tag.
     *
     * @param string $name Nama Tag
     */
    public function index($name)
    {
        // Normalisasi nama tag agar cocok dengan database (Format: Headline Case)
        $tag = Tag::whereName(Str::lower(Str::headline($name)))->first();

        if ($tag) {
            $posts = $tag->posts()->paginate(10);
            $tag = Str::lower(Str::headline($name));
            return view("frontend.tag.index", compact("posts", "tag"));
        }

        // Redirect jika tag tidak ditemukan
        return redirect()->route("frontend.home");
    }
}
