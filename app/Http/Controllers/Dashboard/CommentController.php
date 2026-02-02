<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    // Menampilkan daftar komentar
    public function index()
    {
        if (Auth::user()->role == 3) {
            // Admin: Lihat semua komentar
            $comments = Comment::with([
                "post" => function ($q) {
                    $q->withTrashed();
                },
                "user"
            ])->orderBy("id", "DESC")->paginate(20);
        } else {
            // User Biasa: Lihat komentar di postingan mereka saja
            $comments = Comment::with([
                "post" => function ($q) {
                    $q->withTrashed();
                },
                "user"
            ])->whereHas('post', function ($q) {
                $q->withTrashed()->where("user_id", Auth::id());
            })->orderBy("id", "DESC")->paginate(20);
        }
        return view("dashboard.comment.index", compact("comments"));
    }

    public function show(string $id)
    {
        $comment = Comment::with([
            "user",
            "post" => function ($q) {
                $q->withTrashed();
            }
        ])->find($id);
        if ($comment && Gate::allows("update-comment", $comment)) {
            return view("dashboard.comment.show", compact("comment"));
        }
        return abort(404);
    }

    public function destroy(string $id)
    {
        $comment = Comment::find($id);
        if ($comment && Gate::allows("update-comment", $comment)) {
            $comment->delete();
            return back()->with("success", "Komentar berhasil dihapus!");
        }
        return back()->withErrors("Komentar tidak ditemukan!");
    }

    public function status($id)
    {
        $comment = Comment::find($id);
        if ($comment && Gate::allows("update-comment", $comment)) {
            $comment->status = $comment->status ? "0" : "1";
            $comment->save();
            $alert = $comment->status ? "Komentar diterbitkan!" : "Komentar ditarik kembali ke pending!";
            return back()->with("success", $alert);
        }
        return back()->withErrors("Komentar tidak ditemukan!");
    }

    public function trashed()
    {
        if (Auth::user()->role == 3) {
            $comments = Comment::onlyTrashed()->with([
                "post" => function ($q) {
                    $q->withTrashed();
                },
                "user"
            ])->orderBy("id", "DESC")->paginate(20);
        } else {
            $comments = Comment::onlyTrashed()->with([
                "post" => function ($q) {
                    $q->withTrashed();
                },
                "user"
            ])->whereHas('post', function ($q) {
                $q->withTrashed()->where("user_id", Auth::id());
            })->orderBy("id", "DESC")->paginate(20);
        }
        return view("dashboard.comment.trashed", compact("comments"));
    }

    public function delete($id)
    {
        $comment = Comment::onlyTrashed()->find($id);
        if ($comment && Gate::allows("update-comment", $comment)) {
            $comment->replies()->forceDelete();
            $comment->forceDelete();
            return back()->with("success", "Komentar dihapus permanen!");
        }
        return back()->withErrors("Komentar tidak ditemukan!");
    }

    public function restore($id)
    {
        $comment = Comment::onlyTrashed()->find($id);
        if ($comment && Gate::allows("update-comment", $comment)) {
            if ($comment->post()->withTrashed()->first()->deleted_at) {
                return back()->withErrors("Pulihkan postingannya terlebih dahulu!");
            }
            $comment->restore();
            return back()->with("success", "Komentar berhasil dipulihkan!");
        }
        return back()->withErrors("Komentar tidak ditemukan!");
    }
}
