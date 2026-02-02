<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // Dashboard Utama: Statistik Ringkas
    public function index()
    {
        $posts = Post::count(); // Jumlah Artikel
        $comments = Comment::count(); // Jumlah Komentar
        $users = User::count(); // Jumlah User
        $categories = Category::count(); // Jumlah Kategori
        return view("dashboard.home.index", compact("posts", "comments", "users", "categories"));
    }
}
