<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Menampilkan profil publik pengguna dan artikel mereka.
     *
     * @param string $username Username pengguna
     */
    public function index($username)
    {
        // Cari user yang statusnya aktif
        $user = User::where("status", true)->where("username", $username)->first();

        if ($user) {
            // Ambil postingan user tersebut
            $posts = $user->posts()->with("category")->where("status", true)->orderBy("id", "DESC")->paginate(10);
            return view("frontend.user.index", compact("user", "posts"));
        }

        return abort(404);
    }
}
