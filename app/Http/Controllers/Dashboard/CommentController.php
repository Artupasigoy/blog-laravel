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
    public function index(Request $request)
    {
        $currentView = $request->get('view', 'all');

        // Base query for counts (respects role permissions)
        $baseQuery = Comment::query();
        if (Auth::user()->role != 3) {
            $baseQuery->whereHas('post', function ($q) {
                $q->withTrashed()->where("user_id", Auth::id());
            });
        }

        // Get counts for tabs
        $countAll = (clone $baseQuery)->count();
        $countPublished = (clone $baseQuery)->where('status', 1)->count();
        $countPending = (clone $baseQuery)->where('status', 0)->count();

        $query = Comment::with([
            "post" => function ($q) {
                $q->withTrashed();
            },
            "user"
        ]);

        if (Auth::user()->role != 3) {
            // User Biasa: Lihat komentar di postingan mereka saja
            $query->whereHas('post', function ($q) {
                $q->withTrashed()->where("user_id", Auth::id());
            });
        }

        // Filter by status
        if ($currentView == 'pending') {
            $query->where('status', 0);
        } elseif ($currentView == 'published') {
            $query->where('status', 1);
        }
        // 'all' shows everything (no filter)

        $comments = $query->orderBy("id", "DESC")->paginate(20);

        return view("dashboard.comment.index", compact("comments", "currentView", "countAll", "countPublished", "countPending"));
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
        $comment = Comment::with(['post' => fn($q) => $q->withTrashed()])->find($id);

        if (!$comment) {
            return back()->withErrors("Error: Komentar dengan ID $id tidak ditemukan.");
        }

        if (Gate::denies("update-comment", $comment)) {
            $postOwner = $comment->post->user_id ?? 'Unknown';
            return back()->withErrors("Akses ditolak! Anda (ID: " . Auth::id() . ") bukan pemilik postingan ini (Owner ID: $postOwner).");
        }

        $comment->delete();
        return back()->with("success", "Komentar berhasil dihapus!");
    }

    public function status($id)
    {
        $comment = Comment::with(['post' => fn($q) => $q->withTrashed()])->find($id);
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

    public function emptyTrash()
    {
        $query = Comment::onlyTrashed();
        if (Auth::user()->role != 3) {
            $query->whereHas('post', function ($q) {
                $q->withTrashed()->where("user_id", Auth::id());
            });
        }

        $comments = $query->get();
        $count = $comments->count();

        if ($count == 0) {
            return back()->withErrors("Tidak ada komentar di sampah!");
        }

        foreach ($comments as $comment) {
            $comment->replies()->forceDelete();
            $comment->forceDelete();
        }

        return back()->with("success", "$count komentar berhasil dihapus permanen dari sampah!");
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

    public function bulkAction(Request $request)
    {
        $request->validate([
            "ids" => "required|array",
            "action" => "required|string|in:delete,approve,pending"
        ]);

        $ids = $request->ids;
        $count = 0;

        foreach ($ids as $id) {
            $comment = Comment::with(['post' => fn($q) => $q->withTrashed()])->find($id);
            if ($comment && Gate::allows("update-comment", $comment)) {
                if ($request->action == "delete") {
                    $comment->delete();
                    $count++;
                } elseif ($request->action == "approve") {
                    if ($comment->status == 0) {
                        $comment->status = 1;
                        $comment->save();
                        $count++;
                    }
                } elseif ($request->action == "pending") {
                    if ($comment->status == 1) {
                        $comment->status = 0;
                        $comment->save();
                        $count++;
                    }
                }
            }
        }

        $message = match ($request->action) {
            "delete" => "$count komentar berhasil dihapus!",
            "approve" => "$count komentar berhasil disetujui!",
            "pending" => "$count komentar berhasil dikembalikan ke pending!",
            default => "Aksi selesai!"
        };
        return back()->with("success", $message);
    }
}
