<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Post;

class HomeController extends Controller
{
    /**
     * Menampilkan Halaman Utama (Homepage).
     */
    public function index()
    {
        // Postingan terbaru (limit 10)
        $recentposts = Post::with("category")->where("status", true)->orderBy("id", "DESC")->paginate(10);

        // Postingan Unggulan/Featured (limit 10)
        $featuredposts = Post::with(["category", "user"])->where("status", true)->where("is_featured", true)->orderBy("id", "DESC")->limit(10)->get();

        // Daftar Kategori (untuk menu/sidebar)
        $categories = Category::where("status", true)->orderBy("title", "ASC")->limit(10)->get();

        return view("frontend.home.index", compact("recentposts", "featuredposts", "categories"));
    }
}
