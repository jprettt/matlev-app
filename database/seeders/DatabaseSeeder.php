<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kriteria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus kriteria lama jika ada
        Kriteria::whereIn('title', [
            'Komitmen & Kebijakan K3',
            'Perencanaan & Identifikasi Risiko',
        ])->delete();

        // Seed Users
        User::updateOrCreate([
            'email' => 'admin@matlev.test',
        ], [
            'name' => 'Admin Master Data',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'unit_kerja' => 'UP2D Suluttenggo',
        ]);

        User::updateOrCreate([
            'email' => 'user@matlev.test',
        ], [
            'name' => 'User Unit Kerja',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'unit_kerja' => 'UP2D Suluttenggo',
        ]);

        User::updateOrCreate([
            'email' => 'atasan@matlev.test',
        ], [
            'name' => 'Atasan Manajemen',
            'password' => Hash::make('password123'),
            'role' => 'atasan',
            'unit_kerja' => 'UP2D Suluttenggo',
        ]);

        foreach ([
            ['email' => 'budi@matlev.test', 'name' => 'Budi Santoso'],
            ['email' => 'andi@matlev.test', 'name' => 'Andi Pratama'],
            ['email' => 'siti@matlev.test', 'name' => 'Siti Rahma'],
        ] as $account) {
            User::updateOrCreate(['email' => $account['email']], [
                'name' => $account['name'],
                'password' => Hash::make('password123'),
                'role' => 'user',
                'unit_kerja' => 'UP2D Suluttenggo',
            ]);
        }

        // Panggil MatlevSeeder di sini
        $this->call([
            MatlevSeeder::class,
        ]);
    }
}