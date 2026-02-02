<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Menangani fitur pencarian artikel.
     *
     * @param Request $request Menerima parameter query 'q'
     */
    public function index(Request $request)
    {
        if ($request->q) {
            $query = $request->q;
            // Cari postingan berdasarkan judul (LIKE query)
            // Catatan: Logic orWhere duplikat di kode asli, bisa dioptimalkan nanti
            $posts = Post::with("category")->whereStatus(true)->where("title", "LIKE", "%{$query}%")->orWhere("title", "LIKE", "%{$query}%")->orderBy("id", "DESC")->paginate(10);
            return view("frontend.search.index", compact("posts", "query"));
        }
        // Redirect ke home jika tidak ada keyword pencarian
        return redirect()->route("frontend.home");
    }
}
