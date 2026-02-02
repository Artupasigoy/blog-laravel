<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    // Tampilkan semua tag dan hitung jumlah artikelnya
    public function index()
    {
        $tags = Tag::withCount([
            "posts" => function ($q) {
                $q->withTrashed();
            }
        ])->orderBy("id", "DESC")->paginate(20);
        $str = Str::class;
        return view("dashboard.tag.index", compact("tags", "str"));
    }

    public function destroy($id)
    {
        $tag = Tag::find($id);
        if ($tag) {
            $tag->delete();
            return back()->with("success", "Tag berhasil dihapus!");
        }
        return back()->withErrors("Tag tidak ditemukan!");
    }
}
