<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Menampilkan halaman arsip kategori.
     *
     * @param string $slug Slug dari kategori yang diminta
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function index($slug)
    {
        // Cari kategori berdasarkan slug dan pastikan statusnya aktif
        $category = Category::where("slug", $slug)->where("status", true)->first();

        if ($category) {
            $str = Str::class;
            // Ambil postingan dalam kategori ini (dengan pagination)
            $posts = $category->posts()->with(["category", "user"])->where("status", true)->orderBy("id", "DESC")->paginate(10);
            return view("frontend.category.index", compact("category", "posts", "str"));
        }

        // Halaman 404 jika kategori tidak ditemukan
        return abort(404);
    }
}
