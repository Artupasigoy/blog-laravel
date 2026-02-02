<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\SignupController;
use App\Http\Controllers\Dashboard\CategoryController as DashboardCategoryController;
use App\Http\Controllers\Dashboard\CommentController as DashboardCommentController;
use App\Http\Controllers\Dashboard\HomeController as DashboardHomeController;
use App\Http\Controllers\Dashboard\MediaController;
use App\Http\Controllers\Dashboard\MenuController;
use App\Http\Controllers\Dashboard\PageController as DashboardPageController;
use App\Http\Controllers\Dashboard\PostController as DashboardPostController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\SiteSettingController;
use App\Http\Controllers\Dashboard\SocialMediaController;
use App\Http\Controllers\Dashboard\TagController as DashboardTagController;
use App\Http\Controllers\Dashboard\UserController as DashboardUserController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\CommentController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\PostController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\TagController;
use App\Http\Controllers\Frontend\UserController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// Rute Frontend (Publik)
// =========================================================================
Route::name("frontend.")->group(function () {
    Route::get("/", [HomeController::class, "index"])->name("home"); // Halaman Utama
    Route::get("/search", [SearchController::class, "index"])->name("search"); // Pencarian
    Route::get("/post/{slug}", [PostController::class, "index"])->name("post"); // Detail Artikel

    // Fitur Komentar
    Route::post("/comment/{id}", [CommentController::class, "index"])->name("comment");
    Route::post("/comment-reply", [CommentController::class, "reply"])->name("comment.reply");

    // Halaman Arsip (Kategori, User, Tag)
    Route::get("/category/{slug}", [CategoryController::class, "index"])->name("category");
    Route::get("/user/{username}", [UserController::class, "index"])->name("user");
    Route::get("/tag/{name}", [TagController::class, "index"])->name("tag");

    // Halaman Statis
    Route::get("/page/{slug}", [PageController::class, "index"])->name("page");
});

// =========================================================================
// Rute Autentikasi (Daftar, Login, Logout)
// =========================================================================
Route::name("auth.")->group(function () {
    // Registrasi
    Route::get("/signup", [SignupController::class, "index"])->name("signup");
    Route::post("/signup", [SignupController::class, "signup"])->name("signup.submit");

    // Login
    Route::get("/login", [LoginController::class, "index"])->name("login");
    Route::post("/login", [LoginController::class, "login"])->name("login.submit");

    // Logout
    Route::post("/logout", [LogoutController::class, "index"])->name("logout");
});

// =========================================================================
// Rute Dashboard (Admin & User Login)
// =========================================================================
// Semua rute di sini memerlukan login (middleware: auth)
Route::name("dashboard.")->prefix("/dashboard")->middleware(["auth"])->group(function () {

    // 1. Dashboard Utama
    Route::get("/", [DashboardHomeController::class, "index"])->name("home");

    // 2. Manajemen Postingan
    Route::prefix("/posts")->name("posts.")->controller(DashboardPostController::class)->group(function () {
        // Aksi Khusus
        Route::get("/{id}/status", "status")->name("status");   // Ubah status (Draft/Publish)
        Route::get("/{id}/featured", "featured")->name("featured"); // Set Unggulan
        Route::get("/{id}/comment", "comment")->name("comment");    // On/Off Komentar

        // Trash Bin (Sampah)
        Route::get("/trashed", "trashed")->name("trashed");
        Route::get("/{id}/restore", "restore")->name("restore");
        Route::delete("/{id}/delete", "delete")->name("delete"); // Hapus Permanen
    });
    Route::resource("/posts", DashboardPostController::class)->except(["show"]);

    // media
    Route::get("/media/api", [MediaController::class, "apiIndex"])->name("media.api");
    Route::resource("/media", MediaController::class)->except(["show", "edit", "update"]);

    // 4. Manajemen Komentar
    Route::prefix("/comments")->name("comments.")->controller(DashboardCommentController::class)->group(function () {
        Route::get("/{id}/status", "status")->name("status");   // Setujui/Tolak
        Route::get("/trashed", "trashed")->name("trashed");
        Route::get("/{id}/restore", "restore")->name("restore");
        Route::delete("/{id}/delete", "delete")->name("delete");
    });
    Route::resource("/comments", DashboardCommentController::class)->only(["index", "show", "destroy"]);

    // 5. Kategori (Hanya Admin)
    Route::prefix("/categories")->name("categories.")->controller(DashboardCategoryController::class)->middleware(["admin"])->group(function () {
        Route::get("/{id}/status", "status")->name("status");
        Route::get("/trashed", "trashed")->name("trashed");
        Route::get("/{id}/restore", "restore")->name("restore");
        Route::delete("/{id}/delete", "delete")->name("delete");
    });
    Route::resource("/categories", DashboardCategoryController::class)->middleware(["admin"]);

    //tags
    Route::prefix("/tags")->name("tags.")->controller(DashboardTagController::class)->middleware(["admin"])->group(function () {
        Route::get("/index", "index")->name("index");
        Route::delete("/{id}/destroy", "destroy")->name("destroy");
    });

    // users
    Route::prefix("/users")->name("users.")->controller(DashboardUserController::class)->middleware(["admin"])->group(function () {
        Route::get("/{id}/status", "status")->name("status");
    });
    Route::resource("/users", DashboardUserController::class)->middleware(["admin"]);

    // pages
    Route::prefix("/pages")->name("pages.")->controller(DashboardPageController::class)->middleware(["admin"])->group(function () {
        Route::get("/{id}/status", "status")->name("status");
        Route::get("/trashed", "trashed")->name("trashed");
        Route::get("/{id}/restore", "restore")->name("restore");
        Route::delete("/{id}/delete", "delete")->name("delete");
    });
    Route::resource("/pages", DashboardPageController::class)->except(["show"])->middleware(["admin"]);

    // 8. Pengaturan Website (Hanya Admin)
    Route::prefix("/settings")->name("settings.")->middleware(["admin"])->group(function () {
        // Pengaturan Situs (Judul, Logo, dll)
        Route::get("/site-settings", [SiteSettingController::class, "index"])->name("site");
        Route::post("/site-settings", [SiteSettingController::class, "update"])->name("site.update");

        // Profil User (Bisa diakses user biasa karena withoutMiddleware 'admin')
        Route::get("/profile", [ProfileController::class, "index"])->withoutMiddleware(["admin"])->name("profile");
        Route::post("/profile", [ProfileController::class, "update"])->withoutMiddleware(["admin"])->name("profile.update");

        // Ganti Password (Semua User)
        Route::get("/change-password", [ProfileController::class, "password"])->withoutMiddleware(["admin"])->name("password");
        Route::post("/change-password", [ProfileController::class, "passwordUpdate"])->withoutMiddleware(["admin"])->name("password.update");

        // Sosial Media Link
        Route::get("/social-media", [SocialMediaController::class, "index"])->name("social.media");
        Route::post("/social-media", [SocialMediaController::class, "add"])->name("social.media.add");
        Route::get("/social-media/{id}/status", [SocialMediaController::class, "status"])->name("social.media.status");
        Route::delete("/social-media/{id}/delete", [SocialMediaController::class, "delete"])->name("social.media.delete");

        // Menu Header & Footer
        Route::get("/menus/header", [MenuController::class, "header"])->name("menus.header");
        Route::post("/menus/header", [MenuController::class, "headerUpdate"])->name("menus.header.update");
        Route::get("/menus/footer", [MenuController::class, "footer"])->name("menus.footer");
        Route::post("/menus/footer", [MenuController::class, "footerUpdate"])->name("menus.footer.update");
    });
});
