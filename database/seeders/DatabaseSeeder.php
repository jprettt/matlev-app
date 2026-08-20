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
        $admin = User::firstOrCreate([
            'email' => 'admin@matlev.test',
        ], [
            'name' => 'Admin Master Data',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'unit_kerja' => 'Pusat',
        ]);

        $user = User::firstOrCreate([
            'email' => 'user@matlev.test',
        ], [
            'name' => 'User Unit Kerja',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'unit_kerja' => 'UID Suluttenggo',
        ]);

        User::firstOrCreate([
            'email' => 'atasan@matlev.test',
        ], [
            'name' => 'Atasan Manajemen',
            'password' => Hash::make('password123'),
            'role' => 'atasan',
            'unit_kerja' => 'Direksi',
        ]);

        $criteria1 = Kriteria::firstOrCreate([
            'code' => '1',
            'title' => 'Komitmen & Kebijakan K3',
        ]);

        $criteria2 = Kriteria::firstOrCreate([
            'code' => '2',
            'title' => 'Perencanaan & Identifikasi Risiko',
        ]);

        $sub1 = Subkriteria::firstOrCreate([
            'criteria_id' => $criteria1->id,
            'code' => '1.1',
        ], [
            'title' => 'Kebijakan K3 dan tata kelola',
            'description' => 'Dokumen kebijakan K3 yang ditetapkan dan disosialisasikan.',
            'pic' => 'Manajer K3',
        ]);

        $sub2 = Subkriteria::firstOrCreate([
            'criteria_id' => $criteria2->id,
            'code' => '2.1',
        ], [
            'title' => 'HIRADC dan evaluasi risiko',
            'description' => 'Dokumen HIRADC yang terdokumentasi dan direview berkala.',
            'pic' => 'Tim K3',
        ]);

        $maturity1 = $sub1->maturityLevels()->firstOrCreate([
            'level' => 1,
        ], [
            'description' => 'Adanya kebijakan dasar dan pengaturan tugas K3.',
            'evidence_requirement' => 'Bukti kebijakan K3 dan SOP dasar.',
        ]);

        $sub1->maturityLevels()->firstOrCreate([
            'level' => 2,
        ], [
            'description' => 'Pelaksanaan monitoring dan pelaporan K3 secara berkala.',
            'evidence_requirement' => 'Laporan evaluasi K3 dan daftar tindak lanjut.',
        ]);

        $sub2->maturityLevels()->firstOrCreate([
            'level' => 1,
        ], [
            'description' => 'Identifikasi bahaya dasar di area kerja.',
            'evidence_requirement' => 'Daftar hazard dan matriks risiko awal.',
        ]);

        $sub2->maturityLevels()->firstOrCreate([
            'level' => 2,
        ], [
            'description' => 'Pengendalian risiko dan penetapan tindakan mitigasi.',
            'evidence_requirement' => 'Dokumen HIRADC dan rencana pengendalian.',
        ]);

        if (! $maturity1->evidenceUpload()->exists()) {
            $maturity1->evidenceUpload()->create([
                'user_id' => $user->id,
                'file_path' => 'sample/placeholder.pdf',
                'original_filename' => 'template-kebijakan-k3.pdf',
                'status' => 'pending',
                'uploaded_at' => now(),
            ]);
        }
    }
}