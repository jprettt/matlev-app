<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kriteria;
use App\Models\Subkriteria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin
        User::create([
            'name' => 'Admin K3 Pusat',
            'email' => 'admin@pln.co.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'unit_kerja' => 'Pusat'
        ]);

        // Akun User / Unit Kerja
        $user = User::create([
            'name' => 'Unit UID Suluttenggo',
            'email' => 'user@pln.co.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'unit_kerja' => 'UID Suluttenggo'
        ]);

        // Data Kriteria Demo
        $k1 = Kriteria::create(['kode_elemen' => '1.1', 'nama_elemen' => 'Komitmen & Kebijakan K3']);
        $k2 = Kriteria::create(['kode_elemen' => '1.2', 'nama_elemen' => 'Perencanaan & Identifikasi Risiko']);

        // Subkriteria / Indikator
        Subkriteria::create([
            'kriteria_id' => $k1->id,
            'user_id' => $user->id,
            'deskripsi_indikator' => 'Dokumen Kebijakan K3 yang ditandatangani oleh Manajemen Tertinggi.',
            'status' => 'Belum Upload'
        ]);

        Subkriteria::create([
            'kriteria_id' => $k2->id,
            'user_id' => $user->id,
            'deskripsi_indikator' => 'Laporan HIRADC (Hazard Identification Risk Assessment and Risk Control).',
            'status' => 'Belum Upload'
        ]);
    }
}