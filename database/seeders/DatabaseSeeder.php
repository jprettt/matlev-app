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
        Kriteria::whereIn('title', [
            'Komitmen & Kebijakan K3',
            'Perencanaan & Identifikasi Risiko',
        ])->delete();

        $admin = User::updateOrCreate([
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
    }
}