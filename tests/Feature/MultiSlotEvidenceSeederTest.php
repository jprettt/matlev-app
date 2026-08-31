<?php

namespace Tests\Feature;

use Database\Seeders\MatlevSeeder;
use Tests\TestCase;

class MultiSlotEvidenceSeederTest extends TestCase
{
    public function test_seeded_evidence_requirements_include_multi_slot_periods()
    {
        $method = new \ReflectionMethod(MatlevSeeder::class, 'parseFixedEvidenceRequirements');
        $method->setAccessible(true);

        $records = $method->invoke(new MatlevSeeder());

        $this->assertNotContains('-', array_map(fn ($record) => $record['name'] ?? '', $records));
        $this->assertFalse(
            collect($records)->contains(fn ($record) => trim((string) ($record['name'] ?? '')) === '-')
        );
        $this->assertFalse(
            collect($records)->contains(fn ($record) => ($record['sub_criteria_code'] ?? null) === '2.1' && ($record['level'] ?? null) === 3 && in_array(trim((string) ($record['name'] ?? '')), ['Monitoring rencana/realisasi', 'Dokumentasi inspeksi K3'], true))
        );
        $this->assertFalse(
            collect($records)->contains(fn ($record) => str_contains(strtolower((string) ($record['name'] ?? '')), 'tidak ada bukti wajib'))
        );

        $sub42 = null;
        foreach ($records as $record) {
            if (($record['sub_criteria_code'] ?? null) === '4.2' && ($record['level'] ?? null) === 3 && ($record['name'] ?? '') === 'Dokumentasi, absensi, materi edukasi') {
                $sub42 = $record;
                break;
            }
        }

        $this->assertNotNull($sub42);
        $this->assertSame(2, $sub42['periods']);

        $sub61 = null;
        foreach ($records as $record) {
            if (($record['sub_criteria_code'] ?? null) === '6.1' && ($record['level'] ?? null) === 1 && strpos(($record['name'] ?? ''), 'Statistik laporan') !== false) {
                $sub61 = $record;
                break;
            }
        }

        $this->assertNotNull($sub61);
        $this->assertSame(6, $sub61['periods']);
    }
}
