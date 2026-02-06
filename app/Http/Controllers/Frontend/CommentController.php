<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class CommentController extends Controller
{
    /**
     * Menyimpan komentar baru pada postingan.
     * 
     * @param Request $request Data input komentar
     * @param int $id ID Postingan
     */
    public function index(Request $request, $id)
    {
        // Validasi postingan: harus aktif dan komentar diizinkan
        $post = Post::where("status", true)->where("enable_comment", true)->find($id);

        if ($post) {
            $rules = [
                "message" => ["required", "string", "min:3"],
            ];

            // Aturan tambahan untuk Guest (Tamu)
            if (!Auth::check()) {
                $rules["name"] = ["required", "string", "min:3", "max:100"];
                $rules["email"] = ["required", "email:rfc", "max:255"];
            }
            $validated = $request->validate($rules);

            // Cek duplikasi email: Jika email guest sudah terdaftar sebagai user, minta login
            if (!Auth::check() && User::where("email", $validated["email"])->first()) {
                return redirect()->route("frontend.post", $post->slug . "#comment-form")->withErrors("Email ini sudah terdaftar. Silakan login terlebih dahulu!");
            }

            if (Auth::check()) {
                // User Login: Komentar butuh moderasi (Status = 0)
                Comment::create([
                    "message" => $validated["message"],
                    "post_id" => $post->id,
                    "user_id" => Auth::id(),
                    "status" => "0"
                ]);
                return redirect()->route("frontend.post", $post->slug . "#comment-form")->with("success", "Komentar terkirim! Menunggu moderasi admin.");
            } else {
                // Guest: Komentar butuh moderasi (Status = 0)
                Comment::create([
                    "message" => $validated["message"],
                    "name" => $validated["name"],
                    "email" => $validated["email"],
                    "post_id" => $post->id,
                    "status" => "0"
                ]);
                return redirect()->route("frontend.post", $post->slug . "#comment-form")->with("success", "Komentar terkirim! Menunggu moderasi admin.");
            }
        }
        return abort(404);
    }

    public function reply(Request $request)
    {
        $rules = [
            "message" => ["required", "string", "min:3"],
            "id" => ["required", "integer"],
        ];
        if (!Auth::check()) {
            $rules["name"] = ["required", "string", "min:3", "max:100"];
            $rules["email"] = ["required", "email:rfc", "max:255"];
        }
        $validated = $request->validate($rules);
        $comment = Comment::with("post")->where("id", $validated["id"])->where("status", true)->where("parent_id", null)->first();
        if ($comment && $comment->post && $comment->post->status && $comment->post->enable_comment) {
            if (!Auth::check() && User::where("email", $validated["email"])->first()) {
                return redirect()->route("frontend.post", $comment->post->slug . "#comment-form")->withErrors("Email ini sudah terdaftar. Silakan login sebelum membalas komentar!");
            }
            if (Auth::check()) {
                Comment::create([
                    "message" => $validated["message"],
                    "post_id" => $comment->post->id,
                    "parent_id" => $validated["id"],
                    "user_id" => Auth::id(),
                    "status" => "0"
                ]);
                return redirect()->route("frontend.post", $comment->post->slug . "#comment-form")->with("success", "Balasan terkirim! Menunggu moderasi admin.");
            } else {
                Comment::create([
                    "message" => $validated["message"],
                    "name" => $validated["name"],
                    "email" => $validated["email"],
                    "post_id" => $comment->post->id,
                    "parent_id" => $validated["id"],
                    "status" => "0"
                ]);
                return redirect()->route("frontend.post", $comment->post->slug . "#comment-form")->with("success", "Balasan terkirim! Menunggu moderasi admin.");
            }
        }
        return abort(403);
    }
}
