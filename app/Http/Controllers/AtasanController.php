<?php

namespace App\Http\Controllers;

use App\Models\EvidenceUpload;
use App\Models\Kriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AtasanController extends Controller
{
    public function dashboard()
    {
        $criteria = Kriteria::with(['subKriterias.maturityLevels.evidenceUpload.user'])->get();

        $uploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->orderByDesc('uploaded_at')
            ->get();

        $approved = $uploads->where('status', 'approved')->count();
        $pending = $uploads->where('status', 'pending')->count();
        $rejected = $uploads->where('status', 'rejected')->count();
        $total = $uploads->count();

        $chart = [];
        foreach ($criteria as $item) {
            $slot = 0;
            $ok = 0;

            foreach ($item->subKriterias as $sub) {
                foreach ($sub->maturityLevels as $level) {
                    $slot++;
                    if ($level->evidenceUpload && $level->evidenceUpload->status === 'approved') {
                        $ok++;
                    }
                }
            }

            $chart[] = [
                'label' => $item->title,
                'percent' => $slot > 0 ? round(($ok / $slot) * 100) : 0,
            ];
        }

        return view('atasan.dashboard', compact('criteria', 'uploads', 'approved', 'pending', 'rejected', 'total', 'chart'));
    }

    public function approvedEvidence()
    {
        $uploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->where('status', 'approved')
            ->orderByDesc('uploaded_at')
            ->get();

        return view('atasan.evidence', compact('uploads'));
    }

    public function exportSummary()
    {
        $uploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->orderByDesc('uploaded_at')
            ->get();

        $rows = [
            ['No', 'Nama User', 'Unit Kerja', 'Kriteria', 'Sub Kriteria', 'Level', 'Status', 'Catatan', 'Tanggal Upload'],
        ];

        foreach ($uploads as $index => $upload) {
            $rows[] = [
                $index + 1,
                $upload->user->name ?? '-',
                $upload->user->unit_kerja ?? '-',
                $upload->maturityLevel->subkriteria->kriteria->title ?? '-',
                $upload->maturityLevel->subkriteria->title ?? '-',
                $upload->maturityLevel->level ?? '-',
                ucfirst($upload->status ?? 'pending'),
                $upload->rejection_note ?? '-',
                $upload->uploaded_at ? $upload->uploaded_at->timezone(config('app.timezone'))->format('d-m-Y H:i') . ' WITA' : '-',
            ];
        }

        $filename = 'laporan-eksekutif-' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ]);
    }

    public function exportPdf()
    {
        $uploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->orderByDesc('uploaded_at')
            ->get();

        return view('atasan.export-pdf', compact('uploads'));
    }

    public function exportExcel()
    {
        return $this->exportSummary();
    }
}
