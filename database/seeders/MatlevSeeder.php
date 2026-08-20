<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kriteria;       // Gunakan Kriteria (bukan Criteria)
use App\Models\Subkriteria;    // Gunakan Subkriteria
use App\Models\MaturityLevel;

class MatlevSeeder extends Seeder
{
    public function run(): void
    {
        $criteria1 = Kriteria::create([
            'code' => '1',
            'title' => 'Leadership & Management Commitment'
        ]);

        $sub1 = Subkriteria::create([
            'kriteria_id' => $criteria1->id,
            'code' => '1.1',
            'title' => 'Menyusun RKAP Bidang K3',
            'pic' => 'Perencanaan dan K3'
        ]);

        for ($i = 1; $i <= 5; $i++) {
            MaturityLevel::create([
                'subkriteria_id' => $sub1->id,
                'level' => $i,
                'description' => "Deskripsi Indikator Level $i",
                'evidence_requirement' => "Bukti PDF Dokumen Level $i"
            ]);
        }
    }
}