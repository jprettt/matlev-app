<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MatlevSeeder extends Seeder
{
    private function seedEvidenceRequirementsFromSource(): void
    {
        $fixedRequirements = $this->parseFixedEvidenceRequirements();

        $levels = DB::table('maturity_levels')
            ->select('id', 'level', 'sub_criteria_id')
            ->orderBy('id')
            ->get();

        foreach ($levels as $level) {
            $subCriteria = DB::table('sub_criterias')->where('id', $level->sub_criteria_id)->value('code');
            $entries = collect($fixedRequirements)
                ->filter(fn ($item) => ($item['sub_criteria_code'] ?? null) === $subCriteria && (int) ($item['level'] ?? 0) === (int) $level->level)
                ->values();

            if ($entries->isEmpty()) {
                continue;
            }

            $existingCount = DB::table('evidence_requirements')
                ->where('maturity_level_id', $level->id)
                ->count();

            if ($existingCount > 0) {
                continue;
            }

            foreach ($entries as $index => $entry) {
                $periods = max(1, (int) ($entry['periods'] ?? 1));
                $requirementId = DB::table('evidence_requirements')->insertGetId([
                    'maturity_level_id' => $level->id,
                    'name' => $entry['name'] ?? 'Bukti ' . ($subCriteria ?? 'Subkriteria') . ' Level ' . $level->level,
                    'description' => $entry['description'] ?? trim((string) ($level->overall_description ?? $level->description ?? 'Dokumen pendukung untuk level ' . $level->level)),
                    'is_required' => true,
                    'allowed_file_type' => 'pdf',
                    'allowed_file_types' => 'pdf',
                    'max_file_size' => 10240,
                    'minimum_slots' => $periods,
                    'maximum_slots' => $periods,
                    'evidence_mode' => 'FIXED',
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                for ($slot = 1; $slot <= $periods; $slot++) {
                    DB::table('evidence_slots')->insert([
                        'evidence_requirement_id' => $requirementId,
                        'name' => $periods > 1 ? 'Periode ' . $slot : ($entry['name'] ?? 'Bukti ' . ($subCriteria ?? 'Subkriteria') . ' Level ' . $level->level),
                        'description' => $entry['description'] ?? trim((string) ($level->overall_description ?? $level->description ?? 'Dokumen pendukung untuk level ' . $level->level)),
                        'is_required' => true,
                        'sort_order' => $slot,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function parseFixedEvidenceRequirements(): array
    {
        $source = <<<'TEXT'
## KRITERIA 1: Leadership & Management Commitment

### Sub Kriteria 1.1 — Menyusun RKAP Bidang K3 berdasarkan kajian IBPPR terhadap aktifitas operasional unit

**Level 1**
- Komitmen/kebijakan K3 unit 
- Dokumen IBPPR

**Level 2**
- Komitmen/kebijakan K3 unit — Sudah ditandatangani semua manajemen
- Dokumen IBPPR — Sudah direview 1 tahun sekali
- Dokumen RKAP — Tidak terinci programnya untuk khusus K3 dan masih bergabung dengan anggaran lain (dicek PRK program)

**Level 3**
- Komitmen/kebijakan K3 unit — Ditandatangani semua manajemen dan terdapat kebijakan K3 khusus lainnya sebagai penunjang operasional K3, seperti kebijakan SWA/larangan-larangan lainnya
- IBPPR — Terupdate 1 tahun sekali dan menggambarkan daftar program mitigasi bahaya operasional unit berdasarkan identifikasi bahaya yang dilakukan, dituangkan dalam program kerja K3
- Dokumen penetapan kesediaan anggaran K3 secara terpisah — Dokumen penetapan kesediaan anggaran K3 secara terpisah meskipun masih bersifat operasional rutin (daftar PRK, SK penetapan)

**Level 4**
- Bukti sosialisasi komitmen dan program K3 — Bukti sosialisasi komitmen dan program K3
- Monitoring realisasi dari program kerja — Monitoring realisasi dari program kerja terhadap realisasi penyerapan anggaran sesuai penetapan anggaran K3 per unit/per PRK, dilengkapi catatan histori jika terjadi perubahan perencanaan anggaran K3 selama operasional berjalan

**Level 5**
- OFI dan AFI — OFI dan AFI evaluasi realisasi program kerja tahun sebelumnya

---

### Sub Kriteria 1.2 — Menerapkan Contractor Safety Management System (CSMS)

**Level 1**
- Tidak ada bukti wajib pada level ini

**Level 2**
- Klausul pengaturan CSMS dalam dokumen kontrak
- Dokumen Risk Assessment Pekerjaan yang akan ditenderkan

**Level 3**
- Dokumen Risk Assessment Pekerjaan
- Dokumen bukti pelaksanaan Pra Kualifikasi CSMS
- Bukti PJA, WIP dalam aplikasi CSMS

**Level 4**
- Daftar seluruh Kontraktor
- Daftar Kontraktor bersertifikat CSMS — Bersertifikat CSMS
- Daftar inventaris kontrak pekerjaan — Wajib full cycle CSMS
- Dokumen Pre Job Activity, Work In Progress, Final Evaluation

**Level 5**
- Evaluasi pelaksanaan CSMS, tindak lanjut, dan rekomendasi perbaikan kepada Kontraktor

---

### Sub Kriteria 1.3 — Membangun Sistem Manajemen K3 Terintegrasi

**Level 1**
- Tidak ada bukti wajib pada level ini (kondisi awal/belum ada dokumen)

**Level 2**
- Dokumen Integrasi ISO 45001:2018

**Level 3**
- Dokumen Integrasi ISO 45001:2018 — Dokumen integrasinya telah meliputi seluruh Unit Pelaksana

**Level 4**
- Sertifikat SMK3 PP 50/2012 dan ISO 45001:2018

**Level 5**
- Monitoring tindaklanjut — Monitoring tindaklanjut temuan audit internal maupun audit eksternal

---

## KRITERIA 2: Audit, Assessment and Inspection

### Sub Kriteria 2.1 — Melakukan Inspeksi K3 Manajemen

**Level 1**
- Laporan Investigasi Kecelakaan Kerja
- Berita Acara Klarifikasi

**Level 2**
- Monitoring rencana/realisasi
- Dokumentasi inspeksi K3
- Form Inspeksi K3

**Level 3**
- Monitoring rencana/realisasi
- Dokumentasi inspeksi K3
- Form Inspeksi K3 — Terdapat catatan temuan

**Level 4**
- Dokumentasi laporan inspeksi K3 Manajemen pada Aplikasi INSPEKTA — Dilakukan pada Aplikasi INSPEKTA

**Level 5**
- Dokumentasi laporan inspeksi K3 Manajemen pada Aplikasi INSPEKTA — General Manager, Manajer Unit Pelaksana & Manajer Unit Layanan melaksanakan inspeksi K3 1 kali setiap bulan dan temuannya dilaporkan melalui Aplikasi K3 Korporat (Inspekta)
- Monitoring temuan inspeksi K3 Manajemen di aplikasi INSPEKTA dan monitoring tindak lanjutnya

---

### Sub Kriteria 2.2 — Melakukan Audit Internal SMK3

**Level 1**
- Tidak ada bukti wajib pada level ini (kondisi awal/belum ada dokumen)

**Level 2**
- Daftar Unit bersertifikasi SMK3
- Jadwal Audit Internal
- Berita Acara Audit Internal

**Level 3**
- Daftar Unit bersertifikasi SMK3
- Jadwal Audit Internal — Dilaksanakan sesuai target waktu (1 tahun sekali)
- Berita Acara Audit Internal — Dilaksanakan sesuai target waktu (1 tahun sekali)
- Monitoring tindaklanjut — Monitoring tindaklanjut temuan ketidaksesuaian dan PIC-nya

**Level 4**
- Monitoring tindaklanjut — Monitoring tindaklanjut temuan ketidaksesuaian 100% telah selesai ditindaklanjuti

**Level 5**
- Dokumentasi RTM
- Notulen RTM
- OFI dan AFI

---

### Sub Kriteria 2.3 — Melakukan Audit K3 pada Mitra Kerja

**Level 1**
- Dokumen Audit K3 mitra kerja — Belum melaksanakan Audit K3 Mitra Kerja atau hanya dilakukan pada mitra kerja di Unit Induk

**Level 2**
- Dokumen Audit K3 mitra kerja — Hanya dilakukan pada mitra kerja di Unit Induk dan sebagian mitra kerja Unit Pelaksana

**Level 3**
- Dokumen Audit K3 mitra kerja — Dilakukan pada mitra kerja di Unit Induk dan seluruh mitra kerja di Unit Pelaksana
- Laporan Rekomendasi Perbaikan — Rekomendasi perbaikan dari temuan ketidaksesuaian dan penanggung jawabnya

**Level 4**
- Monitoring realisasi — Monitoring realisasi tindak lanjut temuan 100%

**Level 5**
- OFI dan AFI

---

### Sub Kriteria 2.4 — Melakukan Pengukuran Lingkungan Kerja

**Level 1**
- Dokumen laporan hasil pengukuran lingkungan kerja — Belum melaksanakan pengukuran lingkungan kerja atau pengukuran hanya dilakukan di sebagian Unit

**Level 2**
- Dokumen laporan hasil pengukuran lingkungan kerja — Pengukuran lingkungan kerja hanya dilakukan di Unit Induk dan sebagian Unit Pelaksana

**Level 3**
- Monitoring tindaklanjut — Monitoring tindak lanjut temuan ketidaksesuaian dan PIC-nya

**Level 4**
- Monitoring realisasi — Monitoring realisasi tindak lanjut temuan ketidaksesuaian
- Sertifikat kompetensi pelaksana — Sertifikat kompetensi pelaksana pengukuran lingkungan kerja

**Level 5**
- OFI dan AFI

---

### Sub Kriteria 2.5 — Melakukan Pemeriksaan Kesehatan Pegawai

**Level 1**
- Tidak ada bukti wajib pada level ini (kondisi awal/belum ada dokumen)

**Level 2**
- Daftar pegawai yang ber-hak — Daftar pegawai yang ber-hak di Unit Induk dan sebagian Unit Pelaksana
- Laporan hasil pemeriksaan kesehatan — Laporan hasil pemeriksaan kesehatan di Unit Induk dan sebagian Unit Pelaksana

**Level 3**
- Daftar pegawai yang ber-hak — Daftar pegawai yang ber-hak di Unit Induk dan seluruh Unit Pelaksana
- Laporan hasil pemeriksaan kesehatan — Laporan hasil pemeriksaan kesehatan Unit Induk dan seluruh Unit Pelaksana
- Rekapan hasil pemeriksaan — Rekap 10 penyakit dominan dan rencana pencegahan PAK

**Level 4**
- Monitoring realisasi — Monitoring realisasi pencegahan Penyakit Akibat Kerja (PAK) terhadap 10 penyakit dominan

**Level 5**
- Rekomendasi mutasi atau pemindahan — Rekomendasi mutasi atau pemindahan tempat kerja pegawai yang terjangkit PAK

---

### Sub Kriteria 2.6 — Melakukan Pengukuran Hygiene Factor Mitra Kerja

**Level 1**
- Tidak ada bukti wajib pada level ini (kondisi awal/belum ada dokumen)

**Level 2**
- Dokumen laporan pelaksanaan pengukuran Hygiene Factor — Pengukuran dilaksanakan hanya di Unit Induk dan sebagian Unit Pelaksana

**Level 3**
- Dokumen laporan pelaksanaan pengukuran Hygiene Factor — Pengukuran dilaksanakan di Unit Induk dan seluruh Unit Pelaksana
- Monitoring tindak lanjut — Monitoring tindak lanjut dari temuan ketidaksesuaian dan penanggung jawabnya

**Level 4**
- Monitoring realisasi — Monitoring realisasi tindak lanjut temuan ketidaksesuaian
- Daftar jumlah pekerja masing-masing Mitra Kerja — Jumlah peserta pengukuran mencapai 60% dari jumlah personil Mitra Kerja

**Level 5**
- OFI dan AFI berdasarkan temuan

---

## KRITERIA 3: Penerapan Identifikasi Bahaya, Penilaian dan Pengendalian Risiko (IBPPR)

### Sub Kriteria 3.1 — Menerapkan Ijin Kerja (WP) pada setiap pekerjaan yang memiliki tingkat risiko sesuai hasil Risk Assessment Pekerjaan

**Level 1**
- Laporan Investigasi Kecelakaan Kerja
- Berita Acara Klarifikasi

**Level 2**
- Dokumen Ijin Kerja (WP)
- JSA
- IBPPR
- SOP/IK

**Level 3**
- Dokumen Ijin Kerja
- JSA
- IBPPR
- SOP/IK
- Pengawas Pekerjaan & Pengawas K3 — Pada seluruh jenis pekerjaan

**Level 4**
- Sertifikat Kompetensi Pengawas Pekerjaan & Pengawas K3

**Level 5**
- BA Review SOP/IK — BA Review SOP/IK, IBPPR, JSA

---

### Sub Kriteria 3.2 — Menyediakan Sistem Proteksi Kebakaran Instalasi Ketenagalistrikan sesuai IBPPR

**Level 1**
- Laporan Investigasi Kecelakaan Instalasi (Kebakaran)
- Berita Acara Klarifikasi Kecelakaan Instalasi (Kebakaran)

**Level 2**
- Dokumen IBPPR aktifitas rutin dan non rutin unit — Aktifitas rutin/non rutin operasional unit dan perkantoran di Unit Induk dan sebagian Unit Pelaksana atau Sub Unit Pelaksana

**Level 3**
- Dokumen IBPPR — Aktifitas rutin/non rutin operasional unit dan perkantoran di Unit Induk dan seluruh Unit Pelaksana serta Sub Unit Pelaksana
- Program mitigasi — Program mitigasi bahaya kebakaran operasional unit (preventive action sampai corrective action)
- Program penyediaan dalam pemenuhan regulasi

**Level 4**
- Monitoring kesiapan — Monitoring kesiapan sistem proteksi kebakaran
- Monitoring rencana dan realisasi — Monitoring rencana dan realisasi penyediaan proteksi kebakaran berbasis keselamatan aset unit

**Level 5**
- OFI dan AFI

---

### Sub Kriteria 3.3 — Melaksanakan Simulasi Peralatan Proteksi Kebakaran dan Simulasi Tanggap Darurat

**Level 1**
- Dokumentasi pelaksanaan penggunaan peralatan proteksi kebakaran atau pelaksanaan — Tidak melaksanakan simulasi tanggap darurat, atau hanya melaksanakan penggunaan peralatan proteksi kebakaran, atau hanya melaksanakan simulasi tanggap darurat di Unit Induk

**Level 2**
- IBPPR
- Prosedur tanggap darurat/BCP
- SK Tim Tanggap Darurat
- Daftar peralatan tanggap bencana

**Level 3**
- Dokumen pelaksanaan simulasi tanggap darurat
- Evaluasi efektifitas — Evaluasi efektifitas pelaksanaan simulasi tanggap darurat

**Level 4**
- Monitoring kesiapan — Monitoring kesiapan sistem proteksi kebakaran
- Monitoring kesiapan — Monitoring kesiapan peralatan tanggap bencana dan kompetensi personel tim tanggap darurat

**Level 5**
- Laporan pelaksanaan pelatihan BCP
- Laporan kerjasama simulasi/Surat Kerjasama dan dokumentasi

---

## KRITERIA 4: Safety Training and Education

### Sub Kriteria 4.1 — Melaksanakan Pelatihan K3 Manajemen

**Level 1**
- Dokumen rencana pelatihan K3 — Rencana pelatihan K3 Manajemen Unit Induk dan Manajemen Unit Pelaksana, namun belum dilaksanakan sesuai ketentuan

**Level 2**
- Dokumen rencana pelatihan K3 — Rencana pelatihan K3 Manajemen Unit Induk dan Manajemen Unit Pelaksana, pelatihan dilaksanakan sesuai ketentuan, namun tidak seluruh Manajemen Unit Induk dan Unit Pelaksana mengikuti pelatihan
- Dokumentasi pelatihan, absensi pelatihan, dan materi pelatihan — Tidak seluruh Manajemen Unit Induk dan Unit Pelaksana mengikuti pelatihan

**Level 3**
- Dokumen rencana pelatihan K3 — Rencana pelatihan K3 Manajemen Unit Induk dan Manajemen Unit Pelaksana, pelatihan dilaksanakan sesuai ketentuan, serta seluruh Manajemen Unit Induk dan Unit Pelaksana mengikuti pelatihan
- Dokumentasi — Seluruh Manajemen Unit Induk dan Unit Pelaksana mengikuti pelatihan

**Level 4**
- Evaluasi pelaksanaan pelatihan

**Level 5**
- Laporan pelatihan — Laporan pelatihan yang melebihi ketentuan
- Sertifikat — Sertifikat K3 BNSP/Kemenaker

---

### Sub Kriteria 4.2 — Melakukan Edukasi K3 Internal (Pegawai dan Karyawan Mitra Kerja)

**Level 1**
- Kronologis Kecelakaan Kerja
- Berita Acara Kecelakaan Kerja

**Level 2**
- Dokumentasi, absensi, materi edukasi — Unit Induk dan sebagian Unit Pelaksana

**Level 3**
- Dokumentasi, absensi, materi edukasi — Unit Induk dan seluruh Unit Pelaksana setiap triwulan (2x)

**Level 4**
- Dokumentasi, absensi, materi edukasi — Unit Induk dan seluruh Unit Pelaksana setiap triwulan, diikuti oleh semua pegawai dan karyawan mitra kerja (2x)
- Evaluasi pelaksanaan edukasi K3

**Level 5**
- Dokumentasi edukasi — Dokumentasi edukasi yang melebihi ketentuan
- Sertifikat Kompetensi Pengawas dan Pelaksana Pekerjaan

---

## KRITERIA 5: Safety Campaign and Communication

### Sub Kriteria 5.1 — Melaksanakan Rapat P2K3

**Level 1**
- Tidak ada bukti wajib

**Level 2**
- SK tim P2K3 — SK tim P2K3 yang disahkan Disnaker setempat, dilaksanakan oleh sebagian unit
- Dokumentasi P2K3
- Daftar hadir — Daftar hadir dari sebagian unit
- Notulen P2K3
- Bukti pengiriman — Bukti pengiriman laporan kepada Disnaker setempat

**Level 3**
- SK tim P2K3 — SK tim P2K3 yang disahkan Disnaker setempat, dilaksanakan oleh seluruh unit
- Daftar Hadir — Daftar Hadir yang menunjukkan kehadiran pimpinan unit/ketua P2K3 dan perwakilan setiap bidang

**Level 4**
- Monitoring tindaklanjut — Monitoring tindaklanjut rapat P2K3 hasil temuan atau pembahasan pada rapat P2K3

**Level 5**
- Monitoring tindaklanjut rapat P2K3 — Monitoring tindaklanjut rapat P2K3 hasil temuan atau pembahasan, serta 100% telah selesai ditindaklanjuti

---

### Sub Kriteria 5.2 — Melakukan Edukasi dan Upaya Pencegahan Kecelakaan Masyarakat Umum dan Dampak Aktifitas Ketenagalistrikan terhadap Masyarakat

**Level 1**
- Tidak ada bukti wajib pada level ini (kondisi awal/belum ada dokumen)

**Level 2**
- Dokumentasi

**Level 3**
- Dokumentasi
- Notulen — Notulen hasil pembahasan pelaksanaan edukasi/sosialisasi

**Level 4**
- Jadwal rencana edukasi dan kegiatan upaya pencegahan kecelakaan — Jadwal rencana edukasi dan kegiatan upaya pencegahan kecelakaan masyarakat umum selanjutnya

**Level 5**
- Dokumentasi

---

## KRITERIA 6: Reporting

### Sub Kriteria 6.1 — Melaksanakan Pelaporan pada Aplikasi Inspekta

**Level 1**
- Statistik laporan harian, mingguan, dan bulanan tiap Unit pada Aplikasi Inspekta (per bulan) — Unit tidak melakukan pelaporan Unsafe Act, Unsafe Condition, Nearmiss, dan Accident melalui Aplikasi Inspekta (6x)

**Level 2**
- Statistik laporan harian, mingguan, dan bulanan tiap Unit pada Aplikasi Inspekta (per bulan) — Sebagian besar Unit Induk dan Unit Pelaksana tidak melakukan pelaporan Unsafe Act, Unsafe Condition, Nearmiss, dan Accident melalui Aplikasi Inspekta, dan belum menetapkan User sesuai ketentuan (6x)

**Level 3**
- Data terupdate pegawai dan user active setiap bulannya
- Bukti rata-rata User Active 5% < X < 10%

**Level 4**
- Monitoring Tindaklanjut Temuan
- Data User Active setiap bulan > 10%
- Bukti RCI > 80%
- Notulen — Notulen penyampaian monitoring tindak lanjut temuan dan piramida kecelakaan bulanan (6x)

**Level 5**
- OFI & AFI
- Bukti RCI > 95%
TEXT;

        $lines = preg_split('/\R/', $source);
        $records = [];
        $currentSubCriteriaCode = null;
        $currentLevel = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^###\s+Sub\s+Kriteria\s+([0-9]+\.[0-9]+)\b/ui', $trimmed, $subMatch)) {
                $currentSubCriteriaCode = trim($subMatch[1]);
                $currentLevel = null;
                continue;
            }

            if (preg_match('/^\*\*Level\s+([0-9]+)\*\*$/u', $trimmed, $levelMatch)) {
                $currentLevel = (int) $levelMatch[1];
                continue;
            }

            if ($currentSubCriteriaCode !== null && $currentLevel !== null && preg_match('/^-\s*(.*)$/u', $trimmed, $itemMatch)) {
                $raw = trim($itemMatch[1]);

                if ($raw === '' || $raw === '-' || preg_match('/^[-–—]+$/u', $raw)) {
                    continue;
                }

                if (preg_match('/^(?:Tidak\s+ada\s+bukti\s+wajib|Tidak\s+ada\s+bukti\s+wajib\s+pada\s+level\s+ini|kondisi\s+awal\b|belum\s+ada\s+dokumen\b)/i', $raw)) {
                    continue;
                }

                $normalized = strtolower(str_replace(['/', ' ', '-', '_', '—', '–'], '', $raw));
                if ($currentSubCriteriaCode === '2.1' && $currentLevel === 3 && (
                    str_contains($normalized, 'monitoringrencanarealisasi') ||
                    str_contains($normalized, 'dokumentasiinspeksik3')
                )) {
                    continue;
                }

                if ($currentLevel === 5 && ($raw === '' || preg_match('/^[-–—]+$/u', $raw))) {
                    continue;
                }

                $periods = 1;
                if (preg_match('/\((\d+)\s*x\)/i', $raw, $periodMatch)) {
                    $periods = max(1, (int) $periodMatch[1]);
                }

                $name = $raw;
                $description = $raw;

                if (preg_match('/^(.*?)(?:\s*[—-]\s*)(.+)$/u', $raw, $parts)) {
                    $name = trim($parts[1]);
                    $description = trim($parts[2]);
                }

                $records[] = [
                    'sub_criteria_code' => $currentSubCriteriaCode,
                    'level' => $currentLevel,
                    'name' => $name,
                    'description' => $description,
                    'periods' => $periods,
                ];
            }
        }

        return $records;
    }

    public function run(): void
    {
        // 1. Matikan Foreign Key Constraint & Bersihkan Data Lama
        Schema::disableForeignKeyConstraints();
        DB::table('app_notifications')->truncate();
        DB::table('document_permission_requests')->truncate();
        DB::table('evidence_revisions')->truncate();
        DB::table('evidence_uploads')->truncate();
        DB::table('evidence_slots')->truncate();
        DB::table('evidence_requirements')->truncate();
        DB::table('maturity_levels')->truncate();
        DB::table('sub_criterias')->truncate();
        DB::table('criterias')->truncate();
        Schema::enableForeignKeyConstraints();

        $now = now();

        // 2. Isi Data Kriteria (tabel criterias)
        DB::table('criterias')->insert([
            ['id' => 1, 'code' => 'K-01', 'title' => 'Leadership & Management Commitment', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'K-02', 'title' => 'Audit, Assessment and Inspection', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'K-03', 'title' => 'Penerapan Identifikasi Bahaya, Penilaian dan Pengendalian Risiko (IBPPR)', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'code' => 'K-04', 'title' => 'Safety Training and Education', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'code' => 'K-05', 'title' => 'Safety Campaign and Communication', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'code' => 'K-06', 'title' => 'Reporting', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 3. Isi Data Sub Kriteria (tabel sub_criterias)
        DB::table('sub_criterias')->insert([
            ['id' => 1, 'criteria_id' => 1, 'code' => '1.1', 'title' => 'Menyusun RKAP Bidang K3 berdasarkan kajian IBPPR terhadap aktifitas operasional unit', 'description' => 'Program kerja dan Anggaran bidang K3 di Unit Induk dan Unit Pelaksana tertuang dalam RKAP', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'criteria_id' => 1, 'code' => '1.2', 'title' => 'Menerapan Contractor Safety Management System (CSMS)', 'description' => 'Proses pengadaan barang dan jasa telah menerapkan seluruh tahapan CSMS', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'criteria_id' => 1, 'code' => '1.3', 'title' => 'Membangun Sistem Manajemen K3 Terintegrasi', 'description' => 'Pengelolaan K3 telah menerapkan Sistem Manajemen K3 Terintegrasi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'criteria_id' => 2, 'code' => '2.1', 'title' => 'Melakukan Inspeksi K3 Manajemen', 'description' => 'Pelaksanaan Inspeksi K3 Manajemen', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'criteria_id' => 2, 'code' => '2.2', 'title' => 'Melakukan Audit Internal SMK3', 'description' => 'Pelaksanaan Audit Internal SMK3 PP 50/2012', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'criteria_id' => 2, 'code' => '2.3', 'title' => 'Melakukan Audit K3 pada Mitra Kerja', 'description' => 'Pelaksanaan Audit K3 Mitra Kerja', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'criteria_id' => 2, 'code' => '2.4', 'title' => 'Melakukan Pengukuran Lingkungan Kerja', 'description' => 'Pengukuran Lingkungan Kerja di Tempat Kerja', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'criteria_id' => 2, 'code' => '2.5', 'title' => 'Melakukan Pemeriksaan Kesehatan Pegawai', 'description' => 'Pemeriksaan Kesehatan bagi pegawai diatas 40 tahun dan pekerja pada resiko tinggi.', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'criteria_id' => 2, 'code' => '2.6', 'title' => 'Melakukan Pengukuran Hygiene Factor Mitra Kerja', 'description' => 'Pelaksanaan Pengukuran Hygiene Factor kepada Mitra Kerja', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'criteria_id' => 3, 'code' => '3.1', 'title' => 'Menerapkan Ijin Kerja (WP) pada setiap pekerjaan yang memiliki tingkat risiko sesuai hasil Risk Assessment Pekerjaan', 'description' => 'Penerapan Ijin Kerja (WP) setiap pekerjaan yang berpotensi bahaya / kecelakaan kerja', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'criteria_id' => 3, 'code' => '3.2', 'title' => 'Menyediakan Sistem Proteksi Kebakaran Instalasi Ketenagalistrikan sesuai IBPPR', 'description' => 'Sistem Proteksi Kebakaran mampu memproteksi Aset Properti dan Instalasi Ketenagalistrikan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'criteria_id' => 3, 'code' => '3.3', 'title' => 'Melaksanakan Simulasi Peralatan Proteksi Kebakaran dan Simulasi Tanggap Darurat', 'description' => 'Pelaksanaan simulasi peralatan proteksi kebakaran dan simulasi tanggap darurat', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'criteria_id' => 4, 'code' => '4.1', 'title' => 'Melaksanakan Pelatihan K3 Manajemen', 'description' => 'Pelaksanaan Pelatihan K3 Manajemen', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'criteria_id' => 4, 'code' => '4.2', 'title' => 'Melakukan Edukasi K3 Internal (Pegawai dan Karyawan Mitra Kerja)', 'description' => 'Pelaksanaan Edukasi K3 Internal', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 15, 'criteria_id' => 5, 'code' => '5.1', 'title' => 'Melaksanakan Rapat P2K3', 'description' => 'Pelaksanaan Rapat P2K3', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 16, 'criteria_id' => 5, 'code' => '5.2', 'title' => 'Melakukan Edukasi dan Upaya Pencegahan Kecelakaan Masyarakat Umum dan dampak aktifitas ketenagalistrikan terhadap masyarakat', 'description' => 'Pelaksanaan edukasi dan sosialisasi bahaya listrik kepada Masyarakat Umum', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 17, 'criteria_id' => 6, 'code' => '6.1', 'title' => 'Melaksanakan pelaporan pada Aplikasi Inspekta', 'description' => 'Pelaporan pada Aplikasi INSPEKTA', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 4. Isi Data Indikator Level (tabel maturity_levels)
        DB::table('maturity_levels')->insert([
            // Sub Criteria 1.1 (ID: 1)
            ['sub_criteria_id' => 1, 'level' => 1, 'description' => "Belum terdapat kebijakan/ komitmen manajemen untuk penerapan K3 secara keseluruhan.\nUnit telah menyusun IBPPR dan belum terdapat ketersediaan anggaran pengelolaan K3.", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 1, 'level' => 2, 'description' => "Sudah memiliki kebijakan/ komitmen manajemen untuk penerapan K3, Identifikasi potensi bahaya hanya bersifat dokumen dan tidak diturunkan dalam sebuah program K3. sehingga penyediaan anggaran hanya sebatas adanya anggaran tetapi tidak efektif untuk komitmen penurunan risiko pekerjaan.", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 1, 'level' => 3, 'description' => "Sudah memiliki kebijakan/ komitmen K3 dan juga menerbitkan kebijakan khusus K3 penunjang operasional, Identifikasi potensi bahaya sudah menyeluruh untuk semua aktifitas dan menurunkan sebuah program K3 yang berkaitan dengan mitigasi risiko, dan diterjemahkan dalam sebuah rencana anggaran operasional K3 berdasarkan program K3 yang merupakan sekumpulan program mitigasi risiko kerja di IBPPR", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 1, 'level' => 4, 'description' => "Sudah memiliki kebijakan/ komitmen K3 dan juga menerbitkan kebijakan khusus K3 penunjang operasional, Identifikasi potensi bahaya sudah menyeluruh untuk semua aktifitas dan menurunkan sebuah program K3 yang berkaitan dengan mitigasi risiko dan diinformasikan ke seluruh level pekerja unit, dan diterjemahkan dalam sebuah rencana anggaran operasional K3 berdasarkan program K3 yang merupakan sekumpulan program mitigasi risiko kerja di IBPPR dan dipastikan konsistensi pengelolaan K3 dilihat dari realisasi anggaran k3", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 1, 'level' => 5, 'description' => "Komitmen/ kebijakan K3 sudah menjadi kebutuhan dan menjadi bagian yang tidak terpisahkan dalam operasional unit, program k3 disusun secara terstruktur berdasarkan kajian IBPPR yang rutin direview secara berkala dan komitmen manajemen tentang kepastian dan ketersediaan anggaran pembiayaan K3 sesuai dengan perencanaan yang terlah disiapkan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 1.2 (ID: 2)
            ['sub_criteria_id' => 2, 'level' => 1, 'description' => "belum menerapkan klausul CSMS dalam pelaksanaan Pengadaan barang/jasa sehingga persyaratan Risk Assessment seluruh Pekerjaan yang akan ditenderkan belum menjadi dokumen mandatory persyaratan sebuah pengadaan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 2, 'level' => 2, 'description' => "Melakukan Risk Assessment seluruh Pekerjaan yang akan ditenderkan dan menjadi dokumen mandatory untuk proses pengadaan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 2, 'level' => 3, 'description' => "Melaksanakan CSMS Full cycle terhadap pekerjaan yang wajib melakukan full cycle ( minimal risiko tinggi), dengan memonitoring jumlah pekerjaan yang dilakukan vendor/kontraktor/mitra kerja yang berisiko minimal tinggi sesuai dengan kontrak kerja pengadaan yang terbit + jumlah penerapan full cycle ( berdasarkan jumlah pelaksanaan sampai tahap WIP dan Final Evaluation setiap tahunnya) + target pelaksanaan < 90% jumlah pekerjaan yang wajib di full cycle telah dilakukan CSMS Full cycle minimal sampai tahap WIP", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 2, 'level' => 4, 'description' => "Melaksanakan CSMS Full cycle terhadap pekerjaan yang wajib melakukan full cycle ( minimal risiko tinggi), dengan memonitoring jumlah pekerjaan yang dilakukan vendor/kontraktor/mitra kerja yang berisiko minimal tinggi sesuai dengan kontrak kerja pengadaan yang terbit + jumlah penerapan full cycle ( berdasarkan jumlah pelaksanaan sampai tahap WIP dan Final Evaluation setiap tahunnya) + target pelaksanaan ≥ 90% jumlah pekerjaan yang wajib di full cycle telah dilakukan CSMS Full cycle minimal sampai tahap WIP", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 2, 'level' => 5, 'description' => "90% Kontraktor / Vendor / Mitra Kerja di Unit Induk dan Unit Pelaksana telah bersertifikat CSMS.\n100% jumlah pekerjaan yang wajib di full cycle telah dilakukan CSMS Full cycle minimal sampai tahap WIP\nMelakukan evaluasi pelaksanaan CSMS, tindak lanjut dan rekomendasi perbaikan kepada Kontraktor", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 1.3 (ID: 3)
            ['sub_criteria_id' => 3, 'level' => 1, 'description' => "Belum membangun Sistem Manajemen SNI ISO 45001 : 2018 di Unit Induk", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 3, 'level' => 2, 'description' => "Unit Induk telah membangun Sistem Manajemen SNI ISO 45001:2018 yang terintegrasi dengan SMK3 PP 50/2012 dan menyusun prosedur-prosedurnya, namun integrasinya belum meliputi seluruh Unit Pelaksana", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 3, 'level' => 3, 'description' => "Unit Induk telah membangun Sistem Manajemen Terintegrasi ISO 45001:2018 dengan SMK3 PP 50/2012 dan menyusun prosedur-prosedurnya serta dokumen integrasinya telah meliputi seluruh Unit Pelaksana", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 3, 'level' => 4, 'description' => "Unit Induk telah membangun Sistem Manajemen Terintegrasi ISO 45001:2018 dengan SMK3 PP 50/2012 dan seluruh Unit telah dilakukan Sertifikasi", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 3, 'level' => 5, 'description' => "Unit Induk telah membangun Sistem Manajemen Terintegrasi ISO 45001:2018 dengan SMK3 PP 50/2012 dan seluruh Unit telah dilakukan Sertifikasi serta memiliki monitoring tindaklanjut temuan audit internal maupun audit eksternal", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 2.1 (ID: 4)
            ['sub_criteria_id' => 4, 'level' => 1, 'description' => "Terjadi kecelakaan kerja (Luka Berat, Luka Berat Cacat dan Fatality) inspeksi K3 pada lokasi dan aktifitas pekerjaan terkait tidak efektif dalam mengendalikan risiko.", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 4, 'level' => 2, 'description' => "Memiliki rencana inspeksi K3 General Manager dan Manajer Unit Pelaksana selama 6 bulan atau 1 tahun\nGeneral Manager & Manajer Unit Pelaksana melaksanakan inspeksi K3 sesuai target jumlah dan waktu (1 bulan sekali)", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 4, 'level' => 3, 'description' => "Memiliki rencana inspeksi K3 General Manager dan Manajer Unit Pelaksana selama 6 bulan atau 1 tahun\nGeneral Manager & Manajer Unit Pelaksana melaksanakan inspeksi K3 sesuai target jumlah dan waktu (1 bulan sekali) serta memberikan catatan temuan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 4, 'level' => 4, 'description' => "General Manager, Manajer Unit Pelaksana & Manajer Unit Layanan melaksanakan inspeksi K3 1 kali setiap bulan dan temuannya dilaporkan melalui Aplikasi K3 Korporat (Inspekta)", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 4, 'level' => 5, 'description' => "General Manager, Senior Manager, Manajer Unit Pelaksana, MSB Teknis, Manajer Bagian Teknis & Manajer Unit Layanan melaksanakan inspeksi K3 1 kali setiap bulan dan temuannya dilaporkan melalui Aplikasi K3 Korporat (Inspekta)\nMemiliki monitoring temuan inspeksi K3 Manajemen di aplikasi Inspekta dan monitoring tindak lanjutnya", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 2.2 (ID: 5)
            ['sub_criteria_id' => 5, 'level' => 1, 'description' => "Belum melaksanakan Audit Internal SMK3 atau Pelaksanaan Audit Internal hanya dilaksanakan di Unit Induk namun tidak sesuai dengan target waktu (min 1 tahun sekali)", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 5, 'level' => 2, 'description' => "Memiliki rencana/jadwal Audit Internal Unit Induk dan Unit Pelaksana.\nPelaksanaan Audit Internal dilaksanakan di Unit Induk dan sebagian Unit Pelaksana namun tidak sesuai dengan target waktu (min 1 tahun sekali)", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 5, 'level' => 3, 'description' => "Pelaksanaan Audit Internal dilaksanakan di Unit Induk dan seluruh Unit Pelaksana sesuai dengan target waktu (min 1 tahun sekali), memiliki jadwal tindak lanjut dari temuan ketidaksesuaian dan penanggung jawab tindak lanjutnya", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 5, 'level' => 4, 'description' => "Memiliki jadwal tindak lanjut dari temuan ketidaksesuaian Audit Internal dan penanggung jawab tindak lanjutnya serta realisasi tindak lanjut temuan ketidaksesuaian Audit Internal telah mencapai 100%", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 5, 'level' => 5, 'description' => "Realisasi tindak lanjut temuan ketidaksesuaian mencapai 100%, dan tidak terdapat temuan Major.\nTelah melakukan Tinjauan Manajemen SMK3 serta OFI dan AFI berdasarkan hasil Audit Internal", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 2.3 (ID: 6)
            ['sub_criteria_id' => 6, 'level' => 1, 'description' => "Belum melaksanakan Audit K3 Mitra Kerja atau Audit K3 mitra kerja hanya dilakukan pada mitra kerja di Unit Induk", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 6, 'level' => 2, 'description' => "Audit K3 mitra kerja hanya dilakukan pada mitra kerja di Unit Induk dan sebagian mitra kerja Unit Pelaksana", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 6, 'level' => 3, 'description' => "Audit K3 mitra kerja dilakukan pada mitra kerja di Unit Induk dan seluruh mitra kerja di Unit Pelaksana serta memiliki rekomendasi perbaikan dari temuan ketidaksesuaian dan penanggung jawabnya", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 6, 'level' => 4, 'description' => "Audit K3 mitra kerja dilakukan pada mitra kerja di Unit Induk dan seluruh mitra kerja di Unit Pelaksana serta memiliki monitoring realisasi tindak lanjut atau rekomendasi perbaikan dari temuan ketidaksesuaian dan monitoring realisasi tindak lanjut telah mencapai 100% tidaksesuaian", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 6, 'level' => 5, 'description' => "Audit K3 mitra kerja dilakukan pada mitra kerja di Unit Induk dan seluruh mitra kerja di Unit Pelaksana serta memiliki monitoring realisasi tindak lanjut atau rekomendasi perbaikan dari temuan ketidaksesuaian dan monitoring realisasi tindak lanjut telah mencapai 100% serta OFI dan AFI berdasarkan temuan audit", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 2.4 (ID: 7)
            ['sub_criteria_id' => 7, 'level' => 1, 'description' => "Belum melaksanakan pengukuran lingkungan kerja atau pengukuran lingkungan kerja hanya dilakukan di sebagian Unit", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 7, 'level' => 2, 'description' => "Pengukuran lingkungan kerja hanya dilakukan di Unit Induk dan sebagian Unit Pelaksana", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 7, 'level' => 3, 'description' => "Pengukuran lingkungan kerja dilakukan di Unit Induk dan seluruh Unit Pelaksana serta memiliki monitoring tindak lanjut temuan ketidaksesuaian dan penanggung jawab tindak lanjutnya", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 7, 'level' => 4, 'description' => "Pengukuran lingkungan kerja dilakukan di Unit Induk dan seluruh Unit Pelaksana serta memiliki monitoring tindak lanjut temuan ketidaksesuaian dan penanggung jawab tindak lanjutnya serta monitoring realisasi tindak lanjut temuan ketidaksesuaian\nPelaksana pengukuran lingkungan kerja memiliki sertifikat kompetensi", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 7, 'level' => 5, 'description' => "Pengukuran lingkungan kerja dilakukan di Unit Induk dan seluruh Unit Pelaksana serta memiliki monitoring tindak lanjut temuan ketidaksesuaian dan penanggung jawab tindak lanjutnya serta monitoring realisasi tindak lanjut temuan ketidaksesuaian\nPelaksana pengukuran lingkungan kerja memiliki sertifikat kompetensi\nMemiliki OFI & AFI berdasarkan temuan ketidaksesuaian", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 2.5 (ID: 8)
            ['sub_criteria_id' => 8, 'level' => 1, 'description' => "Belum melaksanakan pemeriksaan kesehatan sesuai ketentuan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 8, 'level' => 2, 'description' => "Pemeriksaan kesehatan dilaksanakan hanya di Unit Induk dan sebagian Unit Pelaksana", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 8, 'level' => 3, 'description' => "Pemeriksaan kesehatan dilaksanakan di Unit Induk dan seluruh Unit Pelaksana serta memiliki jadwal dan pelaksanaan sosialisasi hasil pemeriksaan kesehatan serta memiliki rekap 10 penyakit dominan dan rencana pencegahan Penyakit Akibat Kerja (PAK)", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 8, 'level' => 4, 'description' => "Pemeriksaan kesehatan dilaksanakan di Unit Induk dan seluruh Unit Pelaksana serta memiliki jadwal dan pelaksanaan sosialisasi hasil pemeriksaan kesehatan serta memiliki rekap 10 penyakit dominan dan rencana pencegahan Penyakit Akibat Kerja (PAK) serta monitoring realisasi pencegahan PAK", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 8, 'level' => 5, 'description' => "Pemeriksaan kesehatan dilaksanakan di Unit Induk dan seluruh Unit Pelaksana serta memiliki jadwal dan pelaksanaan sosialisasi hasil pemeriksaan kesehatan serta memiliki rekap 10 penyakit dominan dan rencana pencegahan Penyakit Akibat Kerja (PAK) serta monitoring realisasinya dan rekomendasi mutasi atau pemindahan tempat kerja Pegawai yang terjangkit PAK", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 2.6 (ID: 9)
            ['sub_criteria_id' => 9, 'level' => 1, 'description' => "Belum melaksanakan pengukuran Hygiene Factor Mitra Kerja (TAD)", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 9, 'level' => 2, 'description' => "Pengukuran Hygiene Factor Mitra Kerja dilaksanakan hanya di Unit Induk dan sebagian Unit Pelaksana", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 9, 'level' => 3, 'description' => "Pengukuran Hygiene Factor Mitra Kerja dilaksanakan di Unit Induk dan seluruh Unit Pelaksana serta memiliki monitoring tindak lanjut dari temuan ketidaksesuaian dan penanggung jawabnya", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 9, 'level' => 4, 'description' => "Pengukuran Hygiene Factor Mitra Kerja dilaksanakan di Unit Induk dan seluruh Unit Pelaksana dengan jumlah peserta mencapai 60% dari jumlah personil Mitra Kerja serta memiliki monitoring tindak lanjut dari temuan ketidaksesuaian dan penanggung jawabnya serta monitoring realisasi tindak lanjut temuan ketidaksesuaianuaian", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 9, 'level' => 5, 'description' => "Pengukuran Hygiene Factor Mitra Kerja dilaksanakan di Unit Induk dan seluruh Unit Pelaksana dengan jumlah peserta mencapai 80% dari jumlah personil Mitra Kerja serta memiliki monitoring tindak lanjut dari temuan ketidaksesuaian dan penanggung jawabnya serta monitoring realisasi tindak lanjut temuan ketidaksesuaian dan OFI AFI berdasarkan temuan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 3.1 (ID: 10)
            ['sub_criteria_id' => 10, 'level' => 1, 'description' => "Apabila terjadi kecelakaan kerja (Luka Berat, Luka Berat Cacat dan Fatality).\nTidak efektif dalam penerapan identifikasi bahaya, penilaian dan pengendalian risiko", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 10, 'level' => 2, 'description' => "Menerapkan Ijin Kerja (WP) pada setiap pekerjaan yang memiliki tingkat risiko moderat, tinggi, sangat tinggi dan ekstrem, yang dilengkapi / dilampiri JSA, IBPPR, SOP / Instruksi Kerja pada sebagian jenis pekerjaan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 10, 'level' => 3, 'description' => "Menerapkan Ijin Kerja (WP) pada setiap pekerjaan yang memiliki tingkat risiko moderat, tinggi, sangat tinggi dan ekstrem, yang dilengkapi / dilampiri JSA, IBPPR, SOP / Instruksi Kerja serta ada pengawas pekerjaan dan pengawas K3 pada seluruh jenis pekerjaan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 10, 'level' => 4, 'description' => "Menerapkan Ijin Kerja (WP) pada setiap pekerjaan yang memiliki tingkat risiko moderat, tinggi, sangat tinggi dan ekstrem, yang dilengkapi / dilampiri JSA, IBPPR, SOP / Instruksi Kerja serta ada pengawas pekerjaan dan pengawas K3 yang kompeten pada seluruh jenis pekerjaan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 10, 'level' => 5, 'description' => "Menerapkan Ijin Kerja (WP) pada setiap pekerjaan yang memiliki tingkat risiko moderat, tinggi, sangat tinggi dan ekstrem, yang dilengkapi / dilampiri JSA, IBPPR, SOP / Instruksi Kerja serta ada pengawas pekerjaan dan pengawas K3 yang kompeten pada seluruh jenis pekerjaan.\nMelakukan review SOP/IK, IBPPR dan JSA minimal 1 kali dalam setahun", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 3.2 (ID: 11)
            ['sub_criteria_id' => 11, 'level' => 1, 'description' => "Jika terjadi kecelakaan instalasi (kebakaran) di Unit Induk atau di Unit Pelaksana atau di Sub Unit Pelaksana serta Instalasi Ketenagalistrikan yang merupakan aset dari Unit", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 11, 'level' => 2, 'description' => "Identifikasi Bahaya, Penilaian dan Pengendalian Resiko (IBPPR) yang mengindentifikasi potensi bahaya kebakaran dari aktifitas rutin/non rutin aktifitas operasional unit ( Gudang, GI, Pusat Listrik/Pembangkit Listrik, dll) dan perkantoran di Unit Induk dan sebagian Unit Pelaksana atau Sub Unit Pelaksana", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 11, 'level' => 3, 'description' => "Identifikasi Bahaya, Penilaian dan Pengendalian Resiko (IBPPR) yang mengindentifikasi potensi bahaya kebakaran dari aktifitas rutin/non rutin aktifitas operasional unit ( Gudang, GI, Pusat Listrik/Pembangkit Listrik, dll) serta perkantoran dan menyusun program mitigasi sampai proses penyediaanya dalam upaya penurunan risiko bahaya kebakaran sesuai standar yang telah ditetapkan ( SPLN Sistem Proteksi Kebakaran/ standar lain) di Unit Induk dan seluruh Unit Pelaksana serta Sub Unit Pelaksana", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 11, 'level' => 4, 'description' => "Identifikasi Bahaya, Penilaian dan Pengendalian Resiko (IBPPR) yang mengindentifikasi potensi bahaya kebakaran dari aktifitas rutin/non rutin aktifitas operasional unit ( Gudang, GI, Pusat Listrik/Pembangkit Listrik, dll) serta perkantoran dan menyusun program mitigasi sampai proses penyediaanya dalam upaya penurunan risiko bahaya kebakaran sesuai standar yang telah ditetapkan ( SPLN Sistem Proteksi Kebakaran/ standar lain) dan memiliki monitoring rencana dan realisasi penyediaan proteksi kebakaran", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 11, 'level' => 5, 'description' => "Identifikasi Bahaya, Penilaian dan Pengendalian Resiko (IBPPR) Instalasi Ketenagalistrikan (Gedung Kantor, Gudang, GI, Pusat Listrik/Pembangkit Listrik) telah mencakup kebutuhan penyediaan proteksi kebakaran di Unit Induk dan seluruh Unit Pelaksana serta Sub Unit Pelaksana dan realisasi penyediaan proteksi kebakaran telah mencapai 100% serta memiliki OFI dan AFI dalam evaluasi pengendalian risiko kebakaran", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 3.3 (ID: 12)
            ['sub_criteria_id' => 12, 'level' => 1, 'description' => "Tidak melaksanakan simulasi Tanggap darurat atau hanya melaksanakan penggunaan peralatan proteksi kebakaran atau hanya melaksanakan simulasi tanggap darurat di Unit Induk", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 12, 'level' => 2, 'description' => "Unit telah menyusun IBPPR terkait potensi bahaya kondisi darurat dan mitigasi bencana alam dan penyusunan panduan penanganan kondisi darurat dan kesiapan sarana prasarana", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 12, 'level' => 3, 'description' => "1. Unit telah menyusun IBPPR terkait potensi bahaya kondisi darurat dan mitigasi bencana alam dan penyusunan panduan penanganan kondisi darurat dan kesiapan sarana prasarana.\n2. Mensimulasikan prosedur tanggap darurat dan tanggap bencana dengan melakukan evaluasi pelaksanaan simulasi minimal 1 tahun", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 12, 'level' => 4, 'description' => "1. Unit telah menyusun IBPPR terkait potensi bahaya kondisi darurat dan mitigasi bencana alam dan penyusunan panduan penanganan kondisi darurat dan kesiapan sarana prasarana.\n2. Mensimulasikan prosedur tanggap darurat dan tanggap bencana dengan melakukan evaluasi pelaksanaan simulasi lebih dari 1 kali dalam setahun \n3. monitoring kesiapan peralatan tanggap bencana dan kompetensi personel tim tanggap darurat", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 12, 'level' => 5, 'description' => "1. Unit telah menyusun IBPPR terkait potensi bahaya kondisi darurat dan mitigasi bencana alam dan penyusunan panduan penanganan kondisi darurat dan kesiapan sarana prasarana.\n2. Mensimulasikan prosedur tanggap darurat dan tanggap bencana dengan melakukan evaluasi pelaksanaan simulasi lebih dari 1 kali dalam setahun \n3. monitoring kesiapan peralatan tanggap bencana dan kompetensi personel tim tanggap darurat\n4. melaksanakan pelatihan terkait BCP untuk para tim tanggap darurat\n5. simulasi tanggap darurat bekerjasama dengan pihak eksternal", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 4.1 (ID: 13)
            ['sub_criteria_id' => 13, 'level' => 1, 'description' => "Memiliki rencana pelatihan K3 Manajemen Unit Induk dan Manajemen Unit Pelaksana, namun belum dilaksanakan sesuai ketentuan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 13, 'level' => 2, 'description' => "Memiliki rencana pelatihan K3 Manajemen Unit Induk dan Manajemen Unit Pelaksana dan pelatihan dilaksanakan sesuai ketentuan, namun tidak seluruh Manajemen Unit Induk dan Unit Pelaksana mengikuti pelatihan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 13, 'level' => 3, 'description' => "Memiliki rencana pelatihan K3 Manajemen Unit Induk dan Manajemen Unit Pelaksana dan pelatihan dilaksanakan sesuai ketentuan serta seluruh Manajemen Unit Induk dan Unit Pelaksana mengikuti pelatihan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 13, 'level' => 4, 'description' => "Memiliki rencana pelatihan K3 Manajemen Unit Induk dan Manajemen Unit Pelaksana dan pelatihan dilaksanakan sesuai ketentuan serta seluruh Manajemen Unit Induk dan Unit Pelaksana mengikuti pelatihan.\nMelakukan evaluasi pelaksanaan pelatihan K3 bagi Manajemen", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 13, 'level' => 5, 'description' => "Jumlah pelaksanaan pelatihan K3 Manajemen melebihi ketentuan.\nMelakukan Sertifikasi K3 bagi Manajemen Unit Induk dan/atau Unit Pelaksana dari BNSP atau Kemenaker", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 4.2 (ID: 14)
            ['sub_criteria_id' => 14, 'level' => 1, 'description' => "Unit Induk dan Unit Pelaksana tidak melaksanakan Edukasi K3 kepada pegawai dan karyawan mitra kerja atau Terjadi kecelakaan kerja pegawai atau karyawan mitra kerja (Luka Berat, Luka Berat Cacat dan Fatality) pelaksanaan edukasi K3 kepada pegawai atau karyawan mitra kerja tidak efektif", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 14, 'level' => 2, 'description' => "Unit Induk dan sebagian Unit Pelaksana melaksanakan Edukasi K3 kepada pegawai dan karyawan mitra kerja", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 14, 'level' => 3, 'description' => "Unit Induk dan seluruh Unit Pelaksana melaksanakan Edukasi K3 kepada pegawai dan karyawan mitra kerja, namun tidak semua pegawai dan karyawan mitra kerja mengikuti edukasi K3", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 14, 'level' => 4, 'description' => "Unit Induk dan seluruh Unit Pelaksana melaksanakan Edukasi K3 kepada pegawai dan karyawan mitra kerja dan diikuti oleh semua pegawai dan karyawan mitra kerja serta melakukan evaluasi pelaksanaan edukasi K3", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 14, 'level' => 5, 'description' => "Unit Induk dan seluruh Unit Pelaksana melaksanakan Edukasi K3 kepada pegawai dan karyawan mitra kerja melebihi dari ketentuan dan diikuti oleh seluruh pegawai dan karyawan mitra kerja.\nSeluruh pelaksana pekerjaan dan pengawas pekerjaan mendapatkan Sertifikasi K3 dari BNSP / Kemenaker / Pusdiklat / Lembaga Sertifikasi Kompetensi lainnya", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 5.1 (ID: 15)
            ['sub_criteria_id' => 15, 'level' => 1, 'description' => "Unit Induk dan Unit Pelaksana tidak melaksanakan Rapat P2K3 setiap bulan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 15, 'level' => 2, 'description' => "Sebagian Unit melaksanakan Rapat P2K3 dilakukan setiap bulan dan dihadiri oleh Ketua P2K3/ pimpinan unit serta mengirimkan laporan P2K3 ke Disnaker (sesuai ketentuan)", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 15, 'level' => 3, 'description' => "Seluruh Unit melaksanakan Rapat P2K3 dilakukan setiap bulan dan dihadiri oleh Ketua P2K3/ pimpinan unit dan perwakilan setiap bidang kerja serta mengirimkan laporan P2K3 ke Disnaker (sesuai ketentuan)", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 15, 'level' => 4, 'description' => "Seluruh Unit melaksanakan Rapat P2K3 dan dihadiri oleh Ketua P2K3/ pimpinan unit dan perwakilan setiap bidang kerja serta mengirimkan laporan P2K3 ke Disnaker (sesuai ketentuan) dan memiliki monitoring tindaklanjut hasil temuan atau pembahasan pada rapat P2K3", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 15, 'level' => 5, 'description' => "Seluruh Unit melaksanakan Rapat P2K3 dan dihadiri oleh Ketua P2K3 serta mengirimkan laporan P2K3 sesuai ketentuan yang berlaku ke Disnaker dan memiliki monitoring tindaklanjut hasil temuan atau pembahasan pada rapat P2K3 serta 100% telah selesai ditindaklanjuti", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 5.2 (ID: 16)
            ['sub_criteria_id' => 16, 'level' => 1, 'description' => "Seluruh Unit tidak melaksanakan Edukasi dan Upaya Pencegahan Kecelakaan Masyarakat Umum", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 16, 'level' => 2, 'description' => "Sebagian besar Unit melaksanakan Edukasi dan Upaya Pencegahan Kecelakaan Masyarakat Umum hanya dengan menyebarkan flyer / stiker / pamflet / spanduk / x-banner atau melalui media cetak / elektronik.\nAtau sebagian besar Unit melaksanakan Edukasi dan Upaya Pencegahan Kecelakaan Masyarakat Umum dengan melakukan kunjungan atau mengadakan pertemuan dengan warga masyarakat umum", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 16, 'level' => 3, 'description' => "Seluruh Unit melaksanakan Edukasi dan Upaya Pencegahan Kecelakaan Masyarakat Umum hanya dengan menyebarkan flyer / stiker / pamflet / spanduk / x-banner atau melalui media cetak / elektronik.\nAtau seluruh Unit melaksanakan Edukasi dan Upaya Pencegahan Kecelakaan Masyarakat Umum dengan melakukan kunjungan atau mengadakan pertemuan dengan warga masyarakat umum sesuai jumlah dan waktu yang telah ditentukan sesuai ketentuan serta menyusun hasil pembahasan (notulen) pelaksanaannya", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 16, 'level' => 4, 'description' => "1. Seluruh Unit melaksanakan sosialisasi bahaya listrik dengan menyebarkan flyer / stiker / pamflet / spanduk / x-banner atau melalui media cetak / elektronik 1 kali setiap bulan,\natau melakukan kunjungan / pertemuan dengan warga masyarakat umum 1 kali setiap triwulan serta menyusun hasil pembahasan (notulen) pelaksanaannya\ndan sosialisasi bahaya listrik melalui televisi atau radio setempat\n2. melakukan survei pemahaman dan efektifitas pelaksanaan sosialisasi\n3. penurunan jumlah KMU sebesar 25% dari tahun sebelumnya", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 16, 'level' => 5, 'description' => "1. Seluruh Unit melaksanakan sosialisasi bahaya listrik dengan menyebarkan flyer / stiker / pamflet / spanduk / x-banner atau melalui media cetak / elektronik 1 kali setiap bulan,\natau melakukan kunjungan / pertemuan dengan warga masyarakat umum 1 kali setiap triwulan serta menyusun hasil pembahasan (notulen) pelaksanaannya\ndan sosialisasi bahaya listrik melalui televisi atau radio setempat\n2. melakukan survei pemahaman dan efektifitas pelaksanaan sosialisasi\n3. penurunan jumlah KMU sebesar 50% dari tahun sebelumnya", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],

            // Sub Criteria 6.1 (ID: 17)
            ['sub_criteria_id' => 17, 'level' => 1, 'description' => "Unit tidak melakukan pelaporan Unsafe Act, Unsafe Condition, Nearmiss dan Accident melalui Aplikasi Inspekta", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 17, 'level' => 2, 'description' => "Sebagian besar Unit Induk dan Unit Pelaksana tidak melakukan pelaporan Unsafe Act, Unsafe Condition, Nearmiss dan Accident melalui Aplikasi Inspekta dan belum menetapkan User sesuai dengan ketentuan", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 17, 'level' => 3, 'description' => "Seluruh Unit (Unit Induk, Unit Pelaksana dan Sub Unit Pelaksana) telah melakukan pelaporan Unsafe Act, Unsafe Condition, Nearmiss dan Accident melalui Aplikasi Inspekta dan telah menetapkan User sesuai dengan ketentuan.\nUnit memonitor jumlah user active setiap bulannya , dengan target rata-rata 5%- 10% User Active dalam satu semester berdasarkan monitoring bulanannya\nmenyusun safety perfomance pyramid setiap bulan dan disampaikan ke seluruh pegawai", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 17, 'level' => 4, 'description' => "1. Tindak lanjut temuan Unit (Unit Induk, Unit Pelaksana dan Sub Unit Pelaksana) telah ditujukan kepada bidang terkait dan telah ditindaklanjuti sesuai dengan batas waktu yang telah ditentukan. dan dimonitor % temuan yang telah ditindaklanjuti terhadap total temuan setiap bulannya dan diinformasikan ke seluruh bidang bersama penyampaian safety perfomance pyramid setiap bulannya\n2. Unit memonitor jumlah user active setiap bulannya , dengan target rata-rata 10% User Active dalam satu semester berdasarkan monitoring bulanannya\n3. Reporting Culture Indeks mencapai 80%", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
            ['sub_criteria_id' => 17, 'level' => 5, 'description' => "Seluruh Unit (Unit Induk, Unit Pelaksana dan Sub Unit Pelaksana) memiliki Mapping Hazard dan Risk berdasarkan hasil temuan dan tindaklanjut serta analisa risiko dalam aplikasi inspekta.\nMemiliki safety perfomance pyramid dan monitoring tindak lanjut temuan dan disampaikan ke seluruh bidang progress penyelesaiannya >90% temuan terselesaikan.\nMemiliki OFI dan AFI yang disusun bersama K3L dan bidang terkait.\nReporting Culture Indeks mencapai 95%", 'evidence_requirement' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('maturity_levels')->whereNull('overall_description')->update([
            'overall_description' => DB::raw('description'),
        ]);
        DB::table('maturity_levels')->whereNull('level_number')->update([
            'level_number' => DB::raw('level'),
        ]);

        $this->seedEvidenceRequirementsFromSource();

        $levels = DB::table('maturity_levels')->get();
        foreach ($levels as $level) {
            $hasEvidence = DB::table('evidence_requirements')->where('maturity_level_id', $level->id)->exists();
            DB::table('maturity_levels')->where('id', $level->id)->update([
                'evidence_mode' => $hasEvidence ? 'FIXED' : 'NONE',
            ]);
        }

        $budi = DB::table('users')->where('email', 'budi@matlev.test')->value('id');
        $andi = DB::table('users')->where('email', 'andi@matlev.test')->value('id');
        $siti = DB::table('users')->where('email', 'siti@matlev.test')->value('id');
        $admin = DB::table('users')->where('email', 'admin@matlev.test')->value('id');
        $seedRequirements = DB::table('evidence_requirements')->orderBy('id')->limit(5)->get();

        foreach ($seedRequirements as $index => $requirement) {
            $status = ['pending', 'approved', 'rejected', 'approved', 'pending'][$index];
            $owner = [$budi, $budi, $andi, $siti, $budi][$index];
            $uploadId = DB::table('evidence_uploads')->insertGetId([
                'maturity_level_id' => $requirement->maturity_level_id,
                'evidence_requirement_id' => $requirement->id,
                'evidence_slot_id' => DB::table('evidence_slots')->where('evidence_requirement_id', $requirement->id)->orderBy('sort_order')->value('id'),
                'user_id' => $owner,
                'file_path' => 'evidence_pdfs/demo-' . $requirement->id . '.pdf',
                'original_filename' => str_replace(' ', '_', $requirement->name) . '_2026.pdf',
                'file_size' => 250000 + ($index * 50000),
                'mime_type' => 'application/pdf',
                'status' => $status,
                'rejection_note' => $status === 'rejected' ? 'Dokumen belum memuat pengesahan dari pejabat terkait.' : null,
                'rejection_reason' => $status === 'rejected' ? 'Dokumen belum memuat pengesahan dari pejabat terkait.' : null,
                'version' => 1,
                'uploaded_at' => now()->subDays(5 - $index),
                'submitted_at' => now()->subDays(5 - $index),
                'is_current' => true,
                'reviewed_at' => $status === 'pending' ? null : now()->subDays(4 - $index),
                'reviewed_by' => $status === 'pending' ? null : $admin,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($index === 2) {
                DB::table('evidence_revisions')->insert([
                    ['evidence_upload_id' => $uploadId, 'user_id' => $andi, 'version_number' => 1, 'file_path' => 'evidence_pdfs/demo-original.pdf', 'original_filename' => 'RKAP_K3_2026.pdf', 'status' => 'rejected', 'rejection_note' => 'Dokumen belum memuat pengesahan.', 'is_current' => false, 'uploaded_at' => now()->subDays(4), 'created_at' => $now, 'updated_at' => $now],
                    ['evidence_upload_id' => $uploadId, 'user_id' => $andi, 'version_number' => 2, 'file_path' => 'evidence_pdfs/demo-revision.pdf', 'original_filename' => 'RKAP_K3_2026_REV1.pdf', 'status' => 'approved', 'rejection_note' => null, 'is_current' => true, 'uploaded_at' => now()->subDays(2), 'created_at' => $now, 'updated_at' => $now],
                ]);
            }
        }

        $permissionUpload = DB::table('evidence_uploads')->where('user_id', $budi)->first();
        if ($permissionUpload) {
            DB::table('document_permission_requests')->insert([
                ['evidence_upload_id' => $permissionUpload->id, 'owner_id' => $budi, 'requester_id' => $andi, 'reason' => 'Dokumen perlu diperbarui karena terdapat revisi terbaru.', 'action' => 'edit', 'status' => 'pending', 'responded_at' => null, 'created_at' => $now, 'updated_at' => $now],
                ['evidence_upload_id' => $permissionUpload->id, 'owner_id' => $budi, 'requester_id' => $siti, 'reason' => 'Pembaruan dokumen unit.', 'action' => 'edit', 'status' => 'approved', 'responded_at' => $now, 'created_at' => $now, 'updated_at' => $now],
                ['evidence_upload_id' => $permissionUpload->id, 'owner_id' => $budi, 'requester_id' => $siti, 'reason' => 'Penggantian berkas lama.', 'action' => 'edit', 'status' => 'rejected', 'responded_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ]);

            DB::table('app_notifications')->insert([
                ['recipient_id' => $budi, 'type' => 'permission_request', 'title' => 'Permintaan izin', 'message' => 'Andi Pratama meminta izin mengganti dokumen.', 'document_id' => $permissionUpload->id, 'request_id' => DB::table('document_permission_requests')->where('requester_id', $andi)->where('status', 'pending')->value('id'), 'target_url' => route('user.kriteria', ['level' => $permissionUpload->maturity_level_id]), 'is_read' => false, 'created_at' => $now, 'updated_at' => $now],
                ['recipient_id' => $andi, 'type' => 'evaluation', 'title' => 'Evidence ditolak', 'message' => 'Dokumen memerlukan revisi.', 'document_id' => $permissionUpload->id, 'request_id' => null, 'target_url' => route('user.kriteria', ['level' => $permissionUpload->maturity_level_id]), 'is_read' => false, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }
}