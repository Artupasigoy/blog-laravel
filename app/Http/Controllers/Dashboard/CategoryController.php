<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount([
            "posts" => function ($q) {
                $q->withTrashed();
            }
        ])->orderBy("title", "ASC")->paginate(20);
        return view("dashboard.category.index", compact("categories"));
    }

    public function create()
    {
        return view("dashboard.category.add");
    }

    // Menyimpan kategori baru
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            "title" => ["required", "string", "max:150"],
            "slug" => ["required", "max:150", "unique:categories,slug"],
            "description" => ["nullable", "string"],
            "image" => ["nullable", "image"],
            "status" => ["required", Rule::in(["0", "1"])],
        ]);

        // Upload gambar jika ada
        if (Arr::has($validated, "image")) {
            $image = $request->file("image");
            $imageName = md5(time() . rand(11111, 99999)) . "." . $image->extension();
            $image->move(public_path("uploads/category"), $imageName);
        }

        // Simpan ke database
        Category::create([
            "title" => $validated["title"],
            "slug" => $validated["slug"],
            "description" => Arr::has($validated, "description") ? $validated["description"] : null,
            "image" => Arr::has($validated, "image") ? $imageName : null,
            "status" => $validated["status"],
        ]);
        return redirect()->route("dashboard.categories.index")->with("success", "Kategori berhasil dibuat!");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    public function edit(string $id)
    {
        $category = Category::find($id);
        if ($category) {
            return view("dashboard.category.edit", compact("category"));
        }
        return back()->withErrors("Kategori tidak ditemukan!");
    }

    public function update(Request $request, string $id)
    {
        $category = Category::find($id);
        if ($category) {
            $validated = $request->validate([
                "title" => ["required", "string", "max:150"],
                "slug" => ["required", "max:150", Rule::unique("categories", "slug")->ignore($id)],
                "description" => ["nullable", "string"],
                "image" => ["nullable", "image"],
                "status" => ["required", Rule::in(["0", "1"])],
            ]);
            $category->title = $validated["title"];
            $category->slug = $validated["slug"];
            $category->description = Arr::has($validated, "description") ? $validated["description"] : null;
            $category->status = $validated["status"];
            if (Arr::has($validated, "image")) {
                $image = $request->file("image");
                $imageName = md5(time() . rand(11111, 99999)) . "." . $image->extension();
                $image->move(public_path("uploads/category"), $imageName);
                if ($category->image) {
                    if (File::exists(public_path("uploads/category/" . $category->image))) {
                        File::delete(public_path("uploads/category/" . $category->image));
                    }
                }
                $category->image = $imageName;
            }
            $category->save();
            return redirect()->route("dashboard.categories.index")->with("success", "Kategori berhasil diperbarui!");
        }
        return back()->withErrors("Kategori tidak ditemukan!");
    }

    public function destroy(string $id)
    {
        $category = Category::find($id);
        if ($category) {
            $category->delete();
            return back()->with("success", "Kategori berhasil dihapus!");
        }
        return back()->withErrors("Kategori tidak ditemukan!");
    }

    public function restore($id)
    {
        $category = Category::onlyTrashed()->find($id);
        if ($category) {
            $category->restore();
            return back()->with("success", "Kategori berhasil dipulihkan!");
        }
        return back()->withErrors("Kategori tidak ditemukan!");
    }

    public function status($id)
    {
        $category = Category::find($id);
        if ($category) {
            $category->status = $category->status ? "0" : "1";
            $category->save();
            $alert = $category->status ? "Kategori diaktifkan!" : "Kategori dinonaktifkan!";
            return back()->with("success", $alert);
        }
        return back()->withErrors("Kategori tidak ditemukan!");
    }

    public function trashed()
    {
        $categories = Category::onlyTrashed()->withCount([
            "posts" => function ($q) {
                $q->withTrashed();
            }
        ])->orderBy("title", "ASC")->paginate(20);
        return view("dashboard.category.trashed", compact("categories"));
    }

    public function delete($id)
    {
        $category = Category::onlyTrashed()->find($id);
        if ($category) {
            if (File::exists(public_path("uploads/category/" . $category->image))) {
                File::delete(public_path("uploads/category/" . $category->image));
            }
            $category->posts()->forceDelete();
            $category->forceDelete();
            return back()->with("success", "Kategori dihapus permanen!");
        }
        return back()->withErrors("Kategori tidak ditemukan!");
    }
}
