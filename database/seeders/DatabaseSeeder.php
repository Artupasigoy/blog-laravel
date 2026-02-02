<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Menjalankan Seeder Database Utama.
     * Mengisi data awal yang diperlukan aplikasi.
     */
    public function run(): void
    {
        // 1. Membuat akun Super Admin default
        // Menggunakan firstOrCreate agar aman dijalankan berkali-kali (idempotent)
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                "name" => "Admin",
                "username" => "admin",
                "role" => 3, // Role 3 = Super Admin (Akses penuh)
                "password" => "admin", // Password default (WAJIB diubah saat naik production)
            ]
        );

        // 2. Mengisi Pengaturan Situs (Site Settings) default jika belum ada
        if (\App\Models\SiteSetting::count() == 0) {
            \App\Models\SiteSetting::create([
                "site_title" => "Oredoo",
                "tagline" => "Proyek Blog Laravel",
                "description" => "Lorem ipsum dolor, sit amet consectetur adipisicing elit...",
                "logo_dark" => "logo_dark.png",
                "logo_light" => "logo_light.png",
                "copyright_text" => "© 2022, Oredoo, All Rights Reserved.",
                "enable_registration" => "1", // 1 = Registrasi User dibuka
            ]);
        }

        // 3. Membuat Menu Navigasi (Header & Footer) default
        if (\App\Models\Menu::count() == 0) {
            \App\Models\Menu::create([
                "header_menu" => json_encode([
                    ["href" => "http://127.0.0.1:8000/", "icon" => "", "text" => "Beranda", "tooltip" => "", "children" => []],
                    ["href" => "#", "icon" => "", "text" => "Tentang Kami", "tooltip" => "", "children" => []],
                    ["href" => "#", "icon" => "", "text" => "Kontak", "tooltip" => "", "children" => []],
                    ["href" => "#", "icon" => "", "text" => "Kebijakan Privasi", "tooltip" => "", "children" => []]
                ]),
                "footer_menu" => json_encode([
                    ["href" => "http://127.0.0.1:8000/", "icon" => "", "text" => "Beranda", "tooltip" => "", "children" => []],
                    ["href" => "#", "icon" => "", "text" => "Tentang Kami", "tooltip" => "", "children" => []],
                    ["href" => "#", "icon" => "", "text" => "Kontak", "tooltip" => "", "children" => []],
                    ["href" => "#", "icon" => "", "text" => "Kebijakan Privasi", "tooltip" => "", "children" => []]
                ]),
            ]);
        }
    }
}
