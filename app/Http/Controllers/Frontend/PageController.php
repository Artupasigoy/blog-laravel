<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Menampilkan halaman statis (contoh: About, Contact).
     *
     * @param string $slug Slug halaman
     */
    public function index($slug)
    {
        $page = Page::whereStatus(true)->whereSlug($slug)->first();

        if ($page) {
            return view("frontend.page.index", compact("page"));
        }

        // 404 jika halaman tidak ditemukan atau tidak aktif
        return abort(404);
    }
}
