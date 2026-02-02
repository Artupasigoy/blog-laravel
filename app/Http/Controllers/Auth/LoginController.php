<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    // Menampilkan halaman Login
    public function index()
    {
        // Jika sudah login, redirect ke dashboard (tidak perlu login lagi)
        if (Auth::check()) {
            return redirect()->route("dashboard.home");
        }
        return view("auth.login");
    }

    // Proses autentikasi Login
    public function login(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route("dashboard.home");
        }

        // Validasi input
        $validated = $request->validate([
            "email_or_username" => ["required"], // Bisa login pakai email atau username
            "password" => ["required"]
        ]);

        // Cari user berdasarkan email atau username
        $user = User::where("username", $validated["email_or_username"])->orWhere("email", $validated["email_or_username"])->first();

        // Cek status akun (harus aktif)
        if ($user && !$user->status) {
            return back()->withErrors("Akun Anda saat ini tidak aktif!");
        }

        // Cek password dan login
        if ($user && Hash::check($validated["password"], $user->password)) {
            Auth::login($user, $request->has("remember")); // Remember me logic
            return redirect()->route("dashboard.home");
        }

        return back()->withErrors("Kombinasi login salah!");
    }
}
