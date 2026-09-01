<?php

namespace App\Http\Controllers;

use App\Models\EvidenceUpload;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Kriteria;
use App\Models\MaturityLevel;
use App\Models\Subkriteria;
use App\Models\DocumentPermissionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $pendingCount = EvidenceUpload::where('status', 'pending')->count();
        $approvedCount = EvidenceUpload::where('status', 'approved')->count();
        $rejectedCount = EvidenceUpload::where('status', 'rejected')->count();
        $totalCount = $pendingCount + $approvedCount + $rejectedCount;

        // Statistik untuk chart atau tampilan
        $documentsByStatus = [
            'approved' => $approvedCount,
            'pending' => $pendingCount,
            'rejected' => $rejectedCount,
        ];

        // Top contributors
        $topContributors = EvidenceUpload::with('user')
            ->select('user_id', DB::raw('COUNT(*) as total_uploads'))
            ->groupBy('user_id')
            ->orderByDesc('total_uploads')
            ->limit(5)
            ->get();

        // Recent uploads (all status)
        $recentUploads = EvidenceUpload::with([
                'user',
                'maturityLevel.subkriteria.kriteria',
                'evidenceRequirement'
            ])
            ->orderBy('uploaded_at', 'desc')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalCount',
            'documentsByStatus',
            'topContributors',
            'recentUploads'
        ));
    }

    public function queue(Request $request)
    {
        ActivityLog::create([
            'actor_id' => Auth::id(),
            'activity_type' => 'verification_opened',
            'status' => 'pending',
            'occurred_at' => now(),
        ]);

        $criterias = Kriteria::with([
            'subKriterias.maturityLevels.evidenceUploads.user',
            'subKriterias.maturityLevels.evidenceUploads.evidenceRequirement',
            'subKriterias.maturityLevels.evidenceRequirements.slots',
            'subKriterias.maturityLevels.evidenceRequirements',
        ])->orderBy('code')->get();

        $selectedCriteria = $criterias->firstWhere('id', $request->query('criteria_id')) ?? $criterias->first();
        $selectedSub = $selectedCriteria?->subKriterias->firstWhere('id', $request->query('sub_criteria_id')) ?? $selectedCriteria?->subKriterias->first();
        $selectedLevel = $selectedSub?->maturityLevels->firstWhere('id', $request->query('level_id')) ?? $selectedSub?->maturityLevels->first();

        foreach ($criterias as $criteria) {
            foreach ($criteria->subKriterias as $sub) {
                $levels = $sub->maturityLevels
                    ->sortBy('level')
                    ->values();

                foreach ($levels as $level) {
                    $pendingUploads = $level->evidenceUploads
                        ->where('status', 'pending')
                        ->when($request->filled('upload_date'), function ($uploads) use ($request) {
                            return $uploads->filter(fn ($upload) => $upload->uploaded_at && $upload->uploaded_at->format('Y-m-d') === $request->upload_date);
                        })
                        ->values();

                    $previousLevels = $levels->where('level', '<', $level->level);
                    $isBlocked = $previousLevels->isNotEmpty() && $previousLevels->contains(function ($previousLevel) {
                        return ! $this->isLevelReadyForReview($previousLevel);
                    });

                    $level->review_status = $isBlocked
                        ? 'yellow'
                        : ($pendingUploads->isNotEmpty() ? 'red' : 'neutral');
                    $level->review_pending_count = $pendingUploads->count();
                    $level->review_blocked = $isBlocked;
                }
            }
        }

        $criteriaOptions = Kriteria::orderBy('code')->get(['id', 'code', 'title']);
        $selectedCriteriaId = $selectedCriteria?->id ?? $criterias->first()?->id ?? null;
        $selectedSubId = $selectedSub?->id ?? $selectedCriteria?->subKriterias->first()?->id ?? null;
        $selectedLevelId = $selectedLevel?->id ?? $selectedSub?->maturityLevels->first()?->id ?? null;

        return view('admin.queue', compact('criterias', 'criteriaOptions', 'selectedCriteriaId', 'selectedSubId', 'selectedLevelId'));
    }

    public function verifikasi(Request $request)
    {
        $criteriaOptions = Kriteria::orderBy('code')->get(['id', 'code', 'title']);
        $subCriteriaOptions = Subkriteria::query()
            ->when($request->filled('criteria_id'), function ($query) use ($request) {
                $query->where('criteria_id', $request->criteria_id);
            })
            ->orderBy('code')
            ->get(['id', 'code', 'title', 'criteria_id']);

        $pendingUploads = EvidenceUpload::with([
                'user',
                'maturityLevel.subkriteria.kriteria',
                'evidenceRequirement'
            ])
            ->where('status', 'pending')
            ->when($request->filled('upload_date'), function ($query) use ($request) {
                $query->whereDate('uploaded_at', $request->upload_date);
            })
            ->when($request->filled('criteria_id'), function ($query) use ($request) {
                $query->whereHas('maturityLevel.subkriteria', function ($subQuery) use ($request) {
                    $subQuery->where('criteria_id', $request->criteria_id);
                });
            })
            ->when($request->filled('sub_criteria_id'), function ($query) use ($request) {
                $query->whereHas('maturityLevel.subkriteria', function ($subQuery) use ($request) {
                    $subQuery->where('id', $request->sub_criteria_id);
                });
            })
            ->when($request->filled('evidence_name'), function ($query) use ($request) {
                $query->whereHas('evidenceRequirement', function ($subQuery) use ($request) {
                    $subQuery->where('name', 'like', '%' . trim($request->evidence_name) . '%');
                });
            })
            ->orderBy('uploaded_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.verifikasi', compact('pendingUploads', 'criteriaOptions', 'subCriteriaOptions'));
    }

    public function activityHistory(Request $request)
    {
        $activityTypes = collect(['upload', 'revision_upload', 'evaluation']);
        $activityLogs = ActivityLog::with(['actor', 'targetUser', 'maturityLevel.subkriteria.kriteria'])
            ->whereIn('activity_type', $activityTypes)
            ->when($request->filled('activity_type'), fn ($query) => $query->where('activity_type', $request->activity_type))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('occurred_at', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('occurred_at', '<=', $request->to_date))
            ->orderByDesc('occurred_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.activity', compact('activityLogs', 'activityTypes'));
    }

    private function isLevelReadyForReview(MaturityLevel $level): bool
    {
        if (strtoupper((string) $level->evidence_mode) === 'NONE') {
            return true;
        }

        if ($level->evidenceUploads()->where('status', 'rejected')->exists()) {
            return false;
        }

        if ($level->evidenceUploads()->where('status', 'pending')->exists()) {
            return false;
        }

        return $level->evidenceUploads()->where('status', 'approved')->exists();
    }

    private function ensureReviewOrder(EvidenceUpload $upload): void
    {
        $level = $upload->maturityLevel;
        if (! $level) {
            return;
        }

        $previousLevels = MaturityLevel::where('sub_criteria_id', $level->sub_criteria_id)
            ->where('level', '<', $level->level)
            ->orderBy('level')
            ->get();

        foreach ($previousLevels as $previousLevel) {
            if (! $this->isLevelReadyForReview($previousLevel)) {
                abort(403, 'Level yang sedang dinilai belum bisa diproses karena level sebelumnya masih belum selesai dinilai.');
            }
        }
    }

    public function verifyUpload(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'rejection_note' => 'required_if:status,rejected|nullable|string|max:1000',
        ]);

        $upload = EvidenceUpload::findOrFail($id);
        abort_if((int) $upload->user_id === (int) Auth::id(), 403, 'Reviewer tidak boleh menilai dokumen yang diunggah sendiri.');

        if (in_array($request->status, ['approved', 'rejected'], true)) {
            $this->ensureReviewOrder($upload);
        }

        $statusBefore = $upload->status;
        $statusAfter = $request->status;
        $upload->update([
            'status' => $statusAfter,
            'rejection_note' => $statusAfter === 'rejected' ? ($request->rejection_note ?? 'Dokumen tidak sesuai dengan persyaratan.') : null,
            'rejection_reason' => $statusAfter === 'rejected' ? ($request->rejection_note ?? 'Dokumen tidak sesuai dengan persyaratan.') : null,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        if (in_array($statusAfter, ['approved', 'rejected'], true)) {
            DocumentPermissionRequest::where('evidence_upload_id', $upload->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'responded_at' => now(),
                ]);
        }

        ActivityLog::create([
            'evidence_upload_id' => $upload->id,
            'maturity_level_id' => $upload->maturity_level_id,
            'actor_id' => Auth::id(),
            'activity_type' => 'evaluation',
            'filename' => $upload->original_filename,
            'status_before' => $statusBefore,
            'status' => $statusAfter,
            'note' => $statusAfter === 'rejected' ? $upload->rejection_note : null,
            'occurred_at' => now(),
        ]);

        if ($request->status !== 'pending') {
            $requirementId = $upload->evidence_requirement_id ?? $upload->evidenceRequirement?->id;
            $criteriaId = $upload->maturityLevel?->subkriteria?->kriteria?->id ?? null;
            $targetUrl = $requirementId
                ? route('user.kriteria', ['level' => $upload->maturity_level_id, 'requirement' => $requirementId, 'criteria_id' => $criteriaId]) . '#requirement-' . $requirementId
                : route('user.kriteria', ['level' => $upload->maturity_level_id, 'criteria_id' => $criteriaId]) . '#level-' . $upload->maturity_level_id;

            AppNotification::create([
                'recipient_id' => $upload->user_id,
                'type' => 'evaluation',
                'title' => 'Berkas telah dinilai',
                'message' => 'Verifikator telah menilai ' . $upload->original_filename . '. Status: ' . ($request->status === 'approved' ? 'Disetujui' : 'Perlu Revisi') . '.',
                'document_id' => $upload->id,
                'target_url' => $targetUrl,
            ]);
        }

        $message = $request->status === 'approved'
            ? 'Dokumen berhasil disetujui.'
            : ($request->status === 'rejected'
                ? 'Dokumen berhasil ditolak dengan catatan alasan.'
                : 'Status dokumen dikembalikan ke pending.');

        $criteria = $upload->maturityLevel?->subkriteria?->kriteria;
        $subCriteria = $upload->maturityLevel?->subkriteria;
        $level = $upload->maturityLevel;

        return redirect()->route('admin.queue', [
            'criteria_id' => $criteria?->id,
            'sub_criteria_id' => $subCriteria?->id,
            'level_id' => $level?->id,
        ])->with('success', $message);
    }

    public function storeCriteria(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20',
            'title' => 'required|string|max:255',
        ]);

        Kriteria::create([
            'code' => $request->code,
            'title' => $request->title,
        ]);

        return back()->with('success', 'Kriteria baru berhasil ditambahkan.');
    }

    public function destroyCriteria($id)
    {
        $criteria = Kriteria::findOrFail($id);
        $criteria->delete();

        return back()->with('success', 'Kriteria berhasil dihapus.');
    }

    public function storeSubcriteria(Request $request)
    {
        $request->validate([
            'criteria_id' => 'required|exists:criterias,id',
            'code' => 'required|string|max:30',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pic' => 'nullable|string|max:255',
        ]);

        Subkriteria::create($request->all());

        return back()->with('success', 'Subkriteria berhasil ditambahkan.');
    }

    public function destroySubcriteria($id)
    {
        $subkriteria = Subkriteria::findOrFail($id);
        $subkriteria->delete();

        return back()->with('success', 'Subkriteria berhasil dihapus.');
    }

    public function storeLevel(Request $request)
    {
        $request->validate([
            'sub_criteria_id' => 'required|exists:sub_criterias,id',
            'level' => 'required|integer|min:1|max:5',
            'description' => 'nullable|string',
            'evidence_requirement' => 'required|string',
        ]);

        MaturityLevel::create($request->all());

        return back()->with('success', 'Level indikator berhasil ditambahkan.');
    }

    public function destroyLevel($id)
    {
        $level = MaturityLevel::findOrFail($id);
        $level->delete();

        return back()->with('success', 'Level indikator berhasil dihapus.');
    }
}