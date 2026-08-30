<?php

namespace App\Http\Controllers;

use App\Models\Kriteria;
use App\Models\MaturityLevel;
use App\Models\EvidenceUpload;
use App\Models\DocumentPermissionRequest;
use App\Models\EvidenceRevision;
use App\Models\EvidenceRequirement;
use App\Models\EvidenceSlot;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\User;
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
        $criterias = Kriteria::with([
            'subKriterias.maturityLevels.evidenceUploads.user',
            'subKriterias.maturityLevels.evidenceUploads.permissionRequests.requester',
            'subKriterias.maturityLevels.evidenceUploads.revisions.user',
            'subKriterias.maturityLevels.evidenceUploads.revisions.deletedBy',
            'subKriterias.maturityLevels.evidenceRequirements.evidenceUploads.user',
            'subKriterias.maturityLevels.evidenceRequirements.evidenceUploads.permissionRequests.requester',
            'subKriterias.maturityLevels.evidenceRequirements.evidenceUploads.revisions.user',
            'subKriterias.maturityLevels.evidenceRequirements.slots.currentEvidence.user',
            'subKriterias.maturityLevels.evidenceRequirements.slots.currentEvidence.revisions.user',
            'subKriterias.maturityLevels.evidenceRequirements.slots.currentEvidence.permissionRequests.requester',
            'subKriterias.maturityLevels.evidenceRequirements.slots.evidenceUploads.user',
            'subKriterias.maturityLevels.evidenceRequirements.slots.evidenceUploads.revisions.user',
        ])->get();
        $pendingPermissionRequests = DocumentPermissionRequest::with(['evidenceUpload.maturityLevel.subkriteria.kriteria', 'requester'])
            ->where('owner_id', Auth::id())
            ->where('action', 'edit')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $totalSlots = 0;
        $completedLevels = 0;
        $totalApproved = 0;
        $totalPending = 0;
        $totalRejected = 0;
        $rejectedItems = [];
        $allHistories = [];

        foreach ($criterias as $crit) {
            foreach ($crit->subKriterias as $sub) {
                foreach ($sub->maturityLevels as $lvl) {
                    $totalSlots++;
                    if ($lvl->statusForUser(Auth::id()) === 'COMPLETED') {
                        $completedLevels++;
                    }

                    $uploads = $lvl->evidenceUploads;
                    foreach ($uploads as $upload) {
                        $status = $upload->status ?? 'pending';

                        if ($status === 'approved') {
                            $totalApproved++;
                        } elseif ($status === 'pending') {
                            $totalPending++;
                        } elseif ($status === 'rejected') {
                            $totalRejected++;
                        }
                    }

                    if ($uploads->isNotEmpty()) {

                        foreach ($uploads as $upload) {
                            $st = $upload->status ?? 'pending';
                            $currentRevision = $upload->revisions->first(fn ($revision) => $revision->is_current && $revision->status === 'pending');
                            if ($st === 'rejected') {
                                $rejectedItems[] = [
                                    'criteria_id' => $crit->id,
                                    'criteria_code' => $crit->code ?? $crit->kode ?? '',
                                    'criteria' => $crit->title ?? $crit->nama ?? 'Kriteria',
                                    'sub_code' => $sub->code ?? $sub->kode ?? '',
                                    'sub' => $sub->title ?? $sub->nama ?? 'Sub Kriteria',
                                    'level' => $lvl->level,
                                    'requirement' => $lvl->evidence_requirement,
                                    'upload' => $upload,
                                    'current_revision' => $currentRevision,
                                ];
                            }

                            $activeRevisionForHistory = $upload->revisions
                                ->first(fn ($revision) => $revision->is_current && $revision->status !== 'deleted');

                            if (! $activeRevisionForHistory) {
                                $allHistories[] = [
                                    'criteria_code' => $crit->code ?? $crit->kode ?? '',
                                    'criteria_title' => $crit->title ?? $crit->nama ?? '',
                                    'sub_code' => $sub->code ?? $sub->kode ?? '',
                                    'sub_title' => $sub->title ?? $sub->nama ?? '',
                                    'level' => $lvl->level,
                                    'requirement' => $lvl->evidence_requirement,
                                    'filename' => $upload->original_filename,
                                    'file_path' => $upload->file_path,
                                    'status' => $st,
                                    'note' => $upload->rejection_note,
                                    'uploader_id' => $upload->user_id,
                                    'uploader' => $upload->user->name ?? 'User',
                                    'time' => $upload->uploaded_at ?? $upload->created_at,
                                ];
                            }

                            foreach ($upload->revisions as $revision) {
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

        $globalPercent = $totalSlots > 0 ? round(($completedLevels / $totalSlots) * 100) : 0;

        $stats = [
            'totalSlots' => $totalSlots,
            'totalCompletedLevels' => $completedLevels,
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
        $data['activityLogs'] = ActivityLog::with([
                'actor',
                'targetUser',
                'maturityLevel.subkriteria.kriteria',
                'evidenceUpload.evidenceRequirement',
            ])
            ->whereIn('activity_type', [
                'upload',
                'delete',
                'permission_request',
                'permission_granted',
                'evaluation',
                'revision_upload',
            ])
            ->whereHas('actor', fn ($query) => $query->whereIn('role', ['user', 'admin']))
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
            'pdf_files' => 'sometimes|required|array|min:1|max:20',
            'pdf_files.*' => 'file|mimes:pdf|max:10240',
            'pdf_file' => 'sometimes|file|mimes:pdf|max:10240',
            'upload_id' => 'nullable|integer|exists:evidence_uploads,id',
        ]);

        $files = $request->file('pdf_files', []);
        if ($request->hasFile('pdf_file')) {
            $files[] = $request->file('pdf_file');
        }
        if (count($files) === 0) {
            return back()->withErrors(['pdf_files' => 'Pilih minimal satu file PDF.']);
        }

        $maturityLevel = MaturityLevel::findOrFail($levelId);
        $uploadPage = route('user.kriteria', ['level' => $maturityLevel->id]);

        if ($maturityLevel->level > 1) {
            $previousLevel = MaturityLevel::where('sub_criteria_id', $maturityLevel->sub_criteria_id)
                ->where('level', $maturityLevel->level - 1)
                ->with('evidenceUploads')
                ->first();

            if (! $previousLevel || ! $previousLevel->hasAllRequiredFiles()) {
                return redirect($uploadPage)->with('error', 'Anda wajib mengunggah dokumen Level ' . ($maturityLevel->level - 1) . ' terlebih dahulu.');
            }
        }

        try {
            $createdUploads = DB::transaction(function () use ($files, $maturityLevel, $request) {
                $rejectedUploads = EvidenceUpload::where('maturity_level_id', $maturityLevel->id)
                    ->where('status', 'rejected')->lockForUpdate()->get();
                if ($request->filled('upload_id')) {
                    $rejectedUploads = $rejectedUploads->filter(fn ($upload) => (int) $upload->id === (int) $request->upload_id)->values();
                }
                $created = [];

                foreach ($files as $index => $file) {
                    $existing = $rejectedUploads->get($index);
                    $filename = uniqid('', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                    $path = $file->storeAs('evidence_pdfs', $filename, 'public');

                    if ($existing) {
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
                        'version_number' => $nextVersion,
                        'file_path' => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'status' => 'pending',
                        'is_current' => true,
                        'uploaded_at' => now(),
                    ]);
                        $created[] = $existing->fresh();
                    } else {
                        $created[] = EvidenceUpload::create([
                        'maturity_level_id' => $maturityLevel->id,
                        'user_id' => Auth::id(),
                        'file_path' => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'status' => 'pending',
                        'version' => 1,
                        'submitted_at' => now(),
                        'is_current' => true,
                        'uploaded_at' => now(),
                        ]);
                    }
                }

                return $created;
            });

            foreach ($createdUploads as $upload) {
                ActivityLog::create([
                    'evidence_upload_id' => $upload->id,
                    'maturity_level_id' => $maturityLevel->id,
                    'actor_id' => Auth::id(),
                    'activity_type' => $upload->revisions()->exists() ? 'revision_upload' : 'upload',
                    'filename' => $upload->original_filename,
                    'status' => $upload->status,
                    'occurred_at' => $upload->uploaded_at ?? now(),
                ]);

                User::where('role', 'admin')->each(function ($admin) use ($upload, $maturityLevel) {
                    $isRevision = $upload->revisions()->exists();
                    AppNotification::create([
                        'recipient_id' => $admin->id,
                        'type' => $isRevision ? 'revision_upload' : 'upload',
                        'title' => $isRevision ? 'Dokumen revisi baru' : 'Dokumen baru diunggah',
                        'message' => $upload->user->name . ' ' . ($isRevision ? 'mengunggah revisi' : 'mengunggah') . ' ' . $upload->original_filename . '.',
                        'document_id' => $upload->id,
                        'target_url' => route('admin.queue'),
                    ]);
                });
            }

            return redirect($uploadPage)->with('success', count($createdUploads) . ' bukti dokumen PDF berhasil diunggah dan sedang menunggu penilaian!');
        } catch (\Exception $e) {
            return redirect($uploadPage)->with('error', $e->getMessage());
        }
    }

    public function uploadEvidenceRequirement(Request $request, EvidenceRequirement $requirement)
    {
        $slot = $requirement->slots()->firstOrFail();

        return $this->uploadEvidenceSlot($request, $slot);
    }

    public function uploadEvidenceSlot(Request $request, EvidenceSlot $slot)
    {
        $requirement = $slot->evidenceRequirement()->with('maturityLevel.subkriteria.kriteria')->firstOrFail();
        $this->ensurePreviousLevelHasUpload($requirement->maturityLevel);
        $request->validate([
            'document' => 'required|file|mimes:' . strtolower($requirement->allowed_file_types ?: $requirement->allowed_file_type) . '|max:' . $requirement->max_file_size,
        ], [
            'document.mimes' => 'File harus berformat ' . strtoupper($requirement->allowed_file_types ?: $requirement->allowed_file_type) . '.',
            'document.max' => 'Ukuran file maksimum ' . round($requirement->max_file_size / 1024, 1) . ' MB.',
        ]);

        $level = $requirement->maturityLevel()->with('evidenceUploads')->firstOrFail();
        $existing = $slot->currentEvidence()->latest('id')->first();
        $isRevisionUpload = (bool) $existing;
        $isOwner = $existing && (int) $existing->user_id === (int) Auth::id();
        $permission = $existing?->permissionRequests()
            ->where('requester_id', Auth::id())
            ->where('action', 'edit')
            ->where('status', 'approved')
            ->whereNull('used_at')
            ->latest('responded_at')
            ->first();

        if ($existing && $existing->status === 'approved') {
            return back()->withErrors(['document' => 'Evidence ini sudah memiliki file aktif dan tidak dapat diganti saat berstatus ' . $existing->status . '.']);
        }

        if ($existing && $existing->status !== 'rejected' && ! $isOwner && ! $permission) {
            abort(403, 'Anda belum mendapat izin untuk mengganti evidence ini.');
        }

        $file = $request->file('document');
        $path = $file->storeAs('evidence_pdfs', uniqid('', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName()), 'public');

        DB::transaction(function () use ($existing, $requirement, $slot, $file, $path, $permission) {
            if (! $existing) {
                $existing = EvidenceUpload::create([
                    'maturity_level_id' => $requirement->maturity_level_id,
                    'evidence_requirement_id' => $requirement->id,
                    'evidence_slot_id' => $slot->id,
                    'user_id' => Auth::id(),
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'status' => 'pending',
                    'uploaded_at' => now(),
                ]);
            } else {
                $nextVersion = ((int) $existing->revisions()->max('version_number')) + 1;
                $activeRevision = $existing->revisions()->where('is_current', true)->first();
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
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'status' => 'pending',
                    'rejection_note' => null,
                        'rejection_reason' => null,
                        'version' => $nextVersion,
                        'submitted_at' => now(),
                        'is_current' => true,
                    'uploaded_at' => now(),
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                ]);
                EvidenceRevision::create([
                    'evidence_upload_id' => $existing->id,
                    'user_id' => Auth::id(),
                    'version_number' => $nextVersion,
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'status' => 'pending',
                    'is_current' => true,
                    'uploaded_at' => now(),
                ]);
                $permission?->update(['used_at' => now()]);
            }
        });

        $criteriaId = $level->subkriteria->kriteria->id;

        $upload = $requirement->evidenceUploads()->latest('id')->firstOrFail();
        ActivityLog::create([
            'evidence_upload_id' => $upload->id,
            'maturity_level_id' => $level->id,
            'actor_id' => Auth::id(),
            'activity_type' => $isRevisionUpload ? 'revision_upload' : 'upload',
            'filename' => $upload->original_filename,
            'status' => $upload->status,
            'occurred_at' => $upload->uploaded_at ?? now(),
        ]);

        return redirect()->route('user.kriteria', [
                'criteria_id' => $criteriaId,
                'level' => $level->id,
                'requirement' => $requirement->id,
            ])
            ->with('success', 'Evidence berhasil dikirim dan sedang menunggu penilaian.');
    }

    private function ensurePreviousLevelHasUpload(MaturityLevel $level): void
    {
        if ($level->level <= 1) {
            return;
        }

        $previousLevel = MaturityLevel::where('sub_criteria_id', $level->sub_criteria_id)
            ->where('level', $level->level - 1)
            ->with('evidenceUploads')
            ->first();

        if (! $previousLevel || ! $previousLevel->hasAllRequiredFiles()) {
            abort(422, 'Anda wajib mengisi bukti Level ' . ($level->level - 1) . ' terlebih dahulu.');
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