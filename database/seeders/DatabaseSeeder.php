<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon; // Jangan lupa import Carbon untuk membuat timestamp

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Cek apakah role super_admin sudah ada
        $adminRole = Role::firstOrCreate(['name' => 'super_admin']);

        // Membuat pengguna admin jika belum ada
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@gmail.com'], // Ganti dengan email admin yang sesuai
            [
                'name' => 'Admin User',
                'password' => Hash::make('1234567890'), // Ganti dengan password yang aman
                'email_verified_at' => Carbon::now(), // Menetapkan waktu verifikasi email (menggunakan Carbon)
            ]
        );

        // Menetapkan role super_admin kepada pengguna ini
        $adminUser->assignRole('super_admin');
    }
}
