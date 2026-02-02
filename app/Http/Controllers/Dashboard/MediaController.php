<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;

class MediaController extends Controller
{
    public function index()
    {
        if (Auth::user()->role == 3) {
            $media = Media::orderBy("id", "DESC")->paginate(20);
        } else {
            $media = User::find(Auth::id())->media()->orderBy("id", "DESC")->paginate();
        }
        return view("dashboard.media.index", compact("media"));
    }

    public function create()
    {
        return view("dashboard.media.add");
    }

    // Upload Media Baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            "image" => ["required", "image"],
        ]);
        $image = $request->file("image");
        $imageName = md5(time() . rand(11111, 99999)) . "." . $image->extension();
        $image->move(public_path("uploads/media"), $imageName);

        // Simpan referensi file ke database
        $media = Media::create([
            "user_id" => Auth::id(),
            "file_name" => $imageName,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Media berhasil diunggah!',
                'data' => $media
            ]);
        }

        return redirect()->route("dashboard.media.index")->with("success", "Media berhasil diunggah!");
    }

    // API: Ambil daftar media untuk modal (JSON)
    public function apiIndex()
    {
        if (Auth::user()->role == 3) {
            $media = Media::orderBy("id", "DESC")->get();
        } else {
            $media = User::find(Auth::id())->media()->orderBy("id", "DESC")->get();
        }
        return response()->json($media);
    }

    public function destroy(string $id)
    {
        $media = Media::find($id);
        if ($media && Gate::allows("update-media", $media)) {
            if (File::exists(public_path("uploads/media/" . $media->file_name))) {
                File::delete(public_path("uploads/media/" . $media->file_name));
            }
            $media->delete();
            return back()->with("success", "Media berhasil dihapus!");
        }
        return back()->withErrors("Media tidak ditemukan!");
    }
}
