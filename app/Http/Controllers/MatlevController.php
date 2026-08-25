<?php

namespace App\Http\Controllers;

use App\Models\Kriteria;
use App\Models\MaturityLevel;
use App\Models\EvidenceUpload;
use App\Models\DocumentPermissionRequest;
use App\Models\EvidenceRevision;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MatlevController extends Controller
{
    /**
     * Helper untuk menghitung statistik data Maturity Level K3
     */
    private function getStatsAndData()
    {
        $criterias = Kriteria::with(['subKriterias.maturityLevels.evidenceUpload.user', 'subKriterias.maturityLevels.evidenceUpload.permissionRequests.requester', 'subKriterias.maturityLevels.evidenceUpload.revisions.user', 'subKriterias.maturityLevels.evidenceUpload.revisions.deletedBy'])->get();
        $pendingPermissionRequests = DocumentPermissionRequest::with(['evidenceUpload.maturityLevel.subkriteria.kriteria', 'requester'])
            ->where('owner_id', Auth::id())
            ->where('action', 'edit')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $totalSlots = 0;
        $totalApproved = 0;
        $totalPending = 0;
        $totalRejected = 0;
        $rejectedItems = [];
        $allHistories = [];

        foreach ($criterias as $crit) {
            foreach ($crit->subKriterias as $sub) {
                foreach ($sub->maturityLevels as $lvl) {
                    $totalSlots++;
                    if ($lvl->evidenceUpload) {
                        $st = $lvl->evidenceUpload->status ?? 'pending';
                            $currentRevision = $lvl->evidenceUpload->revisions->first(fn ($revision) => $revision->is_current && $revision->status === 'pending');
                            if ($st == 'approved') {
                            $totalApproved++;
                        } elseif ($st == 'pending') {
                            $totalPending++;
                            } elseif ($st == 'rejected' || ($st == 'pending' && $currentRevision)) {
                                if ($st === 'rejected') {
                                    $totalRejected++;
                                }
                            $rejectedItems[] = [
                                    'criteria_id' => $crit->id,
                                    'criteria_code' => $crit->code ?? $crit->kode ?? '',
                                    'criteria' => $crit->title ?? $crit->nama ?? 'Kriteria',
                                    'sub_code' => $sub->code ?? $sub->kode ?? '',
                                    'sub' => $sub->title ?? $sub->nama ?? 'Sub Kriteria',
                                    'level' => $lvl->level,
                                    'requirement' => $lvl->evidence_requirement,
                                    'upload' => $lvl->evidenceUpload,
                                    'current_revision' => $currentRevision,
                            ];
                        }

                        $activeRevisionForHistory = $lvl->evidenceUpload->revisions
                            ->first(fn ($revision) => $revision->is_current && $revision->status !== 'deleted');

                        // Kumpulkan riwayat aktivitas
                        if (! $activeRevisionForHistory) {
                            $allHistories[] = [
                            'criteria_code' => $crit->code ?? $crit->kode ?? '',
                            'criteria_title' => $crit->title ?? $crit->nama ?? '',
                            'sub_code' => $sub->code ?? $sub->kode ?? '',
                            'sub_title' => $sub->title ?? $sub->nama ?? '',
                            'level' => $lvl->level,
                            'requirement' => $lvl->evidence_requirement,
                            'filename' => $lvl->evidenceUpload->original_filename,
                            'file_path' => $lvl->evidenceUpload->file_path,
                            'status' => $st,
                            'note' => $lvl->evidenceUpload->rejection_note,
                            'uploader_id' => $lvl->evidenceUpload->user_id,
                            'uploader' => $lvl->evidenceUpload->user->name ?? 'User',
                            'time' => $lvl->evidenceUpload->uploaded_at ?? $lvl->evidenceUpload->created_at,
                            ];
                        }

                        foreach ($lvl->evidenceUpload->revisions as $revision) {
                            if ($activeRevisionForHistory && ! $revision->is_current && $revision->status !== 'deleted') {
                                continue;
                            }

                            $allHistories[] = [
                                'criteria_code' => $crit->code ?? $crit->kode ?? '',
                                'criteria_title' => $crit->title ?? $crit->nama ?? '',
                                'sub_code' => $sub->code ?? $sub->kode ?? '',
                                'sub_title' => $sub->title ?? $sub->nama ?? '',
                                'level' => $lvl->level,
                                'requirement' => $lvl->evidence_requirement,
                                'filename' => $revision->original_filename,
                                'file_path' => $revision->file_path,
                                'status' => $revision->status,
                                'note' => $revision->rejection_note,
                                'uploader_id' => $revision->user_id,
                                'uploader' => $revision->user->name ?? 'User',
                                'time' => $revision->uploaded_at ?? $revision->created_at,
                                'deleted_by' => $revision->deletedBy->name ?? null,
                                'deleted_at' => $revision->deleted_at,
                                'is_revision' => true,
                            ];
                        }
                    }
                }
            }
        }

        // Urutkan riwayat dari yang terbaru
        usort($allHistories, function ($a, $b) {
            return Carbon::parse($b['time'])->timestamp <=> Carbon::parse($a['time'])->timestamp;
        });

        $myHistories = array_values(array_filter($allHistories, function ($item) {
            return (int) ($item['uploader_id'] ?? 0) === (int) Auth::id();
        }));

        $myStats = [
            'total' => count($myHistories),
            'approved' => count(array_filter($myHistories, fn ($item) => ($item['status'] ?? '') === 'approved')),
            'pending' => count(array_filter($myHistories, fn ($item) => ($item['status'] ?? '') === 'pending')),
            'rejected' => count(array_filter($myHistories, fn ($item) => ($item['status'] ?? '') === 'rejected')),
        ];

        $globalPercent = $totalSlots > 0 ? round(($totalApproved / $totalSlots) * 100) : 0;

        $stats = [
            'totalSlots' => $totalSlots,
            'totalApproved' => $totalApproved,
            'totalPending' => $totalPending,
            'totalRejected' => $totalRejected,
            'globalPercent' => $globalPercent,
            'totalUploaded' => $totalApproved + $totalPending + $totalRejected,
        ];

        return compact('criterias', 'stats', 'rejectedItems', 'allHistories', 'myHistories', 'myStats', 'pendingPermissionRequests');
    }

    /**
     * Halaman 1: Beranda & Landing Page dengan Hero Slider (Fore Coffee Theme)
     */
    public function dashboard()
    {
        $data = $this->getStatsAndData();
        return view('user.dashboard', $data);
    }

    /**
     * Halaman 2: Daftar Kriteria & Form Upload Bukti Dokumen
     */
    public function kriteria()
    {
        $data = $this->getStatsAndData();
        return view('user.kriteria', $data);
    }

    /**
     * Halaman 3: Dokumen yang Perlu Revisi (Ditolak)
     */
    public function revisi()
    {
        $data = $this->getStatsAndData();
        return view('user.revisi', $data);
    }

    /**
     * Halaman 4: Riwayat & Log Aktivitas Pengunggahan
     */
    public function riwayat()
    {
        $data = $this->getStatsAndData();
        $data['activityLogs'] = ActivityLog::with(['actor', 'targetUser', 'maturityLevel.subkriteria'])
            ->orderByDesc('occurred_at')
            ->get();

        return view('user.riwayat', $data);
    }

    /**
     * Halaman 5: Panduan Penggunaan & Kerangka Maturity Level K3
     */
    public function panduan()
    {
        $data = $this->getStatsAndData();
        return view('user.panduan', $data);
    }

    /**
     * Backward compatibility alias untuk index
     */
    public function index()
    {
        return $this->dashboard();
    }

    /**
     * Process Upload File Bukti (First-Come First-Served Engine)
     * Tidak mengubah alur kerja upload asli
     */
    public function upload(Request $request, $levelId)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:10240',
        ]);

        $maturityLevel = MaturityLevel::findOrFail($levelId);
        $existingUpload = $maturityLevel->evidenceUpload;
        $uploadPage = route('user.kriteria', ['level' => $maturityLevel->id]);

        if ($maturityLevel->level > 1) {
            $previousLevel = MaturityLevel::where('sub_criteria_id', $maturityLevel->sub_criteria_id)
                ->where('level', $maturityLevel->level - 1)
                ->with('evidenceUpload')
                ->first();

            if (! $previousLevel || ! $previousLevel->evidenceUpload) {
                return redirect($uploadPage)->with('error', 'Anda wajib mengunggah dokumen Level ' . ($maturityLevel->level - 1) . ' terlebih dahulu.');
            }
        }

        if ($maturityLevel->evidenceUpload && $maturityLevel->evidenceUpload->status !== 'rejected') {
            return redirect($uploadPage)->with('error', 'Gagal: Slot indikator kematangan ini sudah diisi oleh ' . $maturityLevel->evidenceUpload->user->name);
        }

        $isRevision = (bool) ($existingUpload && $existingUpload->status === 'rejected');

        try {
            DB::transaction(function () use ($request, $maturityLevel) {
                $existing = EvidenceUpload::where('maturity_level_id', $maturityLevel->id)->lockForUpdate()->first();

                if ($existing && $existing->status !== 'rejected') {
                    throw new \Exception('Slot ini telah diisi oleh rekan tim lain beberapa saat lalu!');
                }

                $file = $request->file('pdf_file');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $path = $file->storeAs('evidence_pdfs', $filename, 'public');

                if ($existing && $existing->status === 'rejected') {
                    $nextVersion = ((int) EvidenceRevision::where('evidence_upload_id', $existing->id)->max('version_number')) + 1;
                    $activeRevision = EvidenceRevision::where('evidence_upload_id', $existing->id)
                        ->where('is_current', true)
                        ->first();
                    if ($activeRevision) {
                        $activeRevision->update(['is_current' => false]);
                    } else {
                        EvidenceRevision::create([
                            'evidence_upload_id' => $existing->id,
                            'user_id' => $existing->user_id,
                            'version_number' => $nextVersion++,
                            'file_path' => $existing->file_path,
                            'original_filename' => $existing->original_filename,
                            'status' => $existing->status,
                            'is_current' => false,
                            'rejection_note' => $existing->rejection_note,
                            'uploaded_at' => $existing->uploaded_at ?? $existing->created_at,
                        ]);
                    }
                    $existing->update([
                        'user_id' => Auth::id(),
                        'file_path' => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'status' => 'pending',
                        'rejection_note' => null,
                        'uploaded_at' => now(),
                    ]);
                    EvidenceRevision::create([
                        'evidence_upload_id' => $existing->id,
                        'user_id' => Auth::id(),
                        'version_number' => $nextVersion + 1,
                        'file_path' => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'status' => 'pending',
                        'is_current' => true,
                        'uploaded_at' => now(),
                    ]);
                } else {
                    EvidenceUpload::create([
                        'maturity_level_id' => $maturityLevel->id,
                        'user_id' => Auth::id(),
                        'file_path' => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'status' => 'pending',
                        'uploaded_at' => now(),
                    ]);
                }
            });

            $upload = $maturityLevel->fresh()->evidenceUpload;
            ActivityLog::create([
                'evidence_upload_id' => $upload->id,
                'maturity_level_id' => $maturityLevel->id,
                'actor_id' => Auth::id(),
                'activity_type' => $isRevision ? 'revision_upload' : 'upload',
                'filename' => $upload->original_filename,
                'status' => $upload->status,
                'occurred_at' => $upload->uploaded_at ?? now(),
            ]);

            return redirect($uploadPage)->with('success', 'Bukti dokumen PDF berhasil diunggah dan sedang menunggu penilaian!');
        } catch (\Exception $e) {
            return redirect($uploadPage)->with('error', $e->getMessage());
        }
    }

    public function exportReceipt()
    {
        $uploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->where('user_id', Auth::id())
            ->orderByDesc('uploaded_at')
            ->get();

        $filename = 'bukti-terima-' . Auth::user()->name . '-' . now()->format('YmdHis') . '.csv';

        $rows = [
            ['No', 'Kriteria', 'Sub Kriteria', 'Level', 'Status Verifikasi', 'Catatan', 'Tanggal Upload'],
        ];

        foreach ($uploads as $index => $upload) {
            $rows[] = [
                $index + 1,
                $upload->maturityLevel->subkriteria->kriteria->title ?? '-',
                $upload->maturityLevel->subkriteria->title ?? '-',
                $upload->maturityLevel->level ?? '-',
                ucfirst($upload->status ?? 'pending'),
                $upload->rejection_note ?? '-',
                $upload->uploaded_at ? $upload->uploaded_at->timezone(config('app.timezone'))->format('d-m-Y H:i') . ' WITA' : '-',
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportReceiptPdf()
    {
        $uploads = EvidenceUpload::with(['maturityLevel.subkriteria.kriteria'])
            ->where('user_id', Auth::id())
            ->orderByDesc('uploaded_at')
            ->get();

        return view('user.receipt-pdf', compact('uploads'));
    }
}