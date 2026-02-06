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

        // SEO Friendly Filename Logic
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        // Sanitize filename: remove special chars, spaces to dashes, lowercase
        $safeName = preg_replace('/[^a-zA-Z0-9]/', '-', $originalName);
        $safeName = preg_replace('/-+/', '-', $safeName); // Replace multiple dashes
        $safeName = trim($safeName, '-');
        $safeName = strtolower($safeName);

        $extension = $image->extension();
        $imageName = $safeName . '.' . $extension;

        // Check for duplicates and increment
        $counter = 1;
        while (File::exists(public_path("uploads/media/" . $imageName))) {
            $imageName = $safeName . '_' . $counter . '.' . $extension;
            $counter++;
        }

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
            // Move file to trash directory
            $sourcePath = public_path("uploads/media/" . $media->file_name);
            $destPath = storage_path("app/trash/" . $media->file_name);

            if (File::exists($sourcePath)) {
                // Ensure trash directory exists
                if (!File::exists(storage_path("app/trash"))) {
                    File::makeDirectory(storage_path("app/trash"), 0755, true);
                }
                File::move($sourcePath, $destPath);
            }

            $media->delete();
            return back()->with("success", "Media berhasil dipindahkan ke sampah!");
        }
        return back()->withErrors("Media tidak ditemukan!");
    }

    public function trashed()
    {
        if (Auth::user()->role == 3) {
            $media = Media::onlyTrashed()->orderBy("deleted_at", "DESC")->paginate(20);
        } else {
            $media = User::find(Auth::id())->media()->onlyTrashed()->orderBy("deleted_at", "DESC")->paginate(20);
        }
        return view("dashboard.media.trashed", compact("media"));
    }

    public function restore($id)
    {
        $media = Media::onlyTrashed()->find($id);
        if ($media && Gate::allows("update-media", $media)) {
            // Move file back to public directory
            $sourcePath = storage_path("app/trash/" . $media->file_name);
            $destPath = public_path("uploads/media/" . $media->file_name);

            if (File::exists($sourcePath)) {
                File::move($sourcePath, $destPath);
            }

            $media->restore();
            return back()->with("success", "Media berhasil dipulihkan!");
        }
        return back()->withErrors("Media tidak ditemukan!");
    }

    public function delete($id)
    {
        $media = Media::onlyTrashed()->find($id);
        if ($media && Gate::allows("update-media", $media)) {
            // Delete file from trash directory
            $trashPath = storage_path("app/trash/" . $media->file_name);
            if (File::exists($trashPath)) {
                File::delete($trashPath);
            }

            // Fallback: Check public path just in case
            $publicPath = public_path("uploads/media/" . $media->file_name);
            if (File::exists($publicPath)) {
                File::delete($publicPath);
            }

            $media->forceDelete();
            return back()->with("success", "Media berhasil dihapus permanen!");
        }
        return back()->withErrors("Media tidak ditemukan!");
    }

    public function viewTrashFile($id)
    {
        $media = Media::onlyTrashed()->find($id);
        if ($media && Gate::allows("update-media", $media)) {
            $path = storage_path("app/trash/" . $media->file_name);
            if (!File::exists($path)) {
                abort(404);
            }
            return response()->file($path);
        }
        abort(403);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!$ids) {
            return back()->withErrors("Tidak ada media yang dipilih!");
        }

        $count = 0;
        foreach ($ids as $id) {
            $media = Media::find($id);
            if ($media && Gate::allows("update-media", $media)) {
                // Move file to trash directory
                $sourcePath = public_path("uploads/media/" . $media->file_name);
                $destPath = storage_path("app/trash/" . $media->file_name);

                if (File::exists($sourcePath)) {
                    // Ensure trash directory exists
                    if (!File::exists(storage_path("app/trash"))) {
                        File::makeDirectory(storage_path("app/trash"), 0755, true);
                    }
                    File::move($sourcePath, $destPath);
                }

                $media->delete();
                $count++;
            }
        }

        return back()->with("success", "$count media berhasil dipindahkan ke sampah!");
    }

    public function emptyTrash()
    {
        if (Auth::user()->role == 3) {
            $media = Media::onlyTrashed()->get();
        } else {
            $media = User::find(Auth::id())->media()->onlyTrashed()->get();
        }

        $count = 0;
        foreach ($media as $item) {
            if (Gate::allows("update-media", $item)) {
                // Delete file from trash directory
                $trashPath = storage_path("app/trash/" . $item->file_name);
                if (File::exists($trashPath)) {
                    File::delete($trashPath);
                }

                $item->forceDelete();
                $count++;
            }
        }

        return back()->with("success", "$count media berhasil dihapus permanen dari sampah!");
    }
}
