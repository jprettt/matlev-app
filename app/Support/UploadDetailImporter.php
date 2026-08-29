<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class UploadDetailImporter
{
    public static function import(string $path): int
    {
        if (! is_file($path)) {
            throw new RuntimeException('File detail upload.xlsx tidak ditemukan.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File detail upload.xlsx tidak dapat dibuka.');
        }

        $sharedXml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
        $sheetXml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        $zip->close();
        if (! $sharedXml || ! $sheetXml) {
            throw new RuntimeException('Struktur workbook upload.xlsx tidak valid.');
        }

        $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $sharedXml->registerXPathNamespace('x', $ns);
        $sheetXml->registerXPathNamespace('x', $ns);
        $shared = [];
        foreach ($sharedXml->xpath('//x:sst/x:si') as $item) {
            $parts = [];
            foreach ($item->children($ns)->t as $text) {
                $parts[] = (string) $text;
            }
            foreach ($item->children($ns)->r as $run) {
                $parts[] = (string) $run->children($ns)->t;
            }
            $shared[] = trim(implode('', $parts));
        }

        $records = [];
        $current = ['criteria' => null, 'sub_code' => null, 'program' => null, 'level' => null];
        foreach ($sheetXml->xpath('//x:sheetData/x:row') as $row) {
            $cells = [];
            foreach ($row->children($ns)->c as $cell) {
                $value = (string) $cell->children($ns)->v;
                $domCell = dom_import_simplexml($cell);
                if ($domCell->getAttribute('t') === 's') {
                    $value = $shared[(int) $value] ?? '';
                }
                $reference = $domCell->getAttribute('r');
                $cells[preg_replace('/\d+/', '', $reference)] = trim($value);
            }
            if (! empty($cells['B'])) {
                $current['criteria'] = $cells['B'];
            }
            if (! empty($cells['C'])) {
                $current['sub_code'] = $cells['C'];
            }
            if (! empty($cells['D'])) {
                $current['program'] = $cells['D'];
            }
            if (! empty($cells['E']) && preg_match('/Level\s+(\d+)/i', $cells['E'], $match)) {
                $current['level'] = (int) $match[1];
            }
            if (! empty($cells['F']) && $current['sub_code'] && $current['level']) {
                $records[] = [
                    ...$current,
                    'file_name' => $cells['F'],
                    'description' => ($cells['G'] ?? '') === '-' ? null : ($cells['G'] ?? null),
                    'period' => $cells['H'] ?? '',
                ];
            }
        }

        $grouped = collect($records)->groupBy(fn ($record) => $record['sub_code'] . ':' . $record['level']);
        $imported = 0;
        foreach ($grouped as $items) {
            $first = $items->first();
            $subCriteriaId = DB::table('sub_criterias')->where('code', $first['sub_code'])->value('id');
            $level = DB::table('maturity_levels')
                ->where('sub_criteria_id', $subCriteriaId)
                ->where('level', $first['level'])
                ->first();
            if (! $subCriteriaId || ! $level) {
                continue;
            }

            DB::table('evidence_requirements')->where('maturity_level_id', $level->id)->delete();
            $hasEvidence = $items->contains(fn ($item) => $item['file_name'] !== '-');
            DB::table('maturity_levels')->where('id', $level->id)->update([
                'evidence_mode' => $hasEvidence ? 'REQUIRED' : 'NONE',
                'overall_description' => $level->overall_description ?: $level->description,
            ]);
            if (! $hasEvidence) {
                continue;
            }

            $requirementOrder = 0;
            foreach ($items as $item) {
                if ($item['file_name'] === '-') {
                    continue;
                }
                $description = $item['description'];
                if ($first['sub_code'] === '1.3' && $first['level'] === 2) {
                    $description = 'Integrasi belum meliputi seluruh Unit Pelaksana.';
                }
                if ($first['sub_code'] === '1.3' && $first['level'] === 3) {
                    $description = 'Integrasi telah meliputi seluruh Unit Pelaksana.';
                }
                preg_match('/(\d+)\s*x/i', $item['period'], $match);
                $periods = max(1, (int) ($match[1] ?? 1));
                $requirementOrder++;
                $requirementId = DB::table('evidence_requirements')->insertGetId([
                    'maturity_level_id' => $level->id,
                    'name' => $item['file_name'],
                    'description' => $description,
                    'is_required' => true,
                    'allowed_file_type' => 'pdf',
                    'allowed_file_types' => 'pdf',
                    'max_file_size' => 10240,
                    'minimum_slots' => $periods,
                    'maximum_slots' => $periods,
                    'evidence_mode' => 'FIXED',
                    'sort_order' => $requirementOrder,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                for ($period = 1; $period <= $periods; $period++) {
                    DB::table('evidence_slots')->insert([
                        'evidence_requirement_id' => $requirementId,
                        'name' => $periods > 1 ? 'Periode ' . $period : $item['file_name'],
                        'description' => $periods > 1 ? 'Upload ' . $item['file_name'] . ' untuk periode ' . $period . '.' : $description,
                        'is_required' => true,
                        'sort_order' => $period,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            $imported++;
        }

        return $imported;
    }
}
