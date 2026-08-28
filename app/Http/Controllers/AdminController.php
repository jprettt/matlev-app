<?php

namespace App\Http\Controllers;

use App\Models\EvidenceUpload;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Kriteria;
use App\Models\MaturityLevel;
use App\Models\Subkriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $pendingCount = EvidenceUpload::where('status', 'pending')->count();
        $approvedCount = EvidenceUpload::where('status', 'approved')->count();
        $rejectedCount = EvidenceUpload::where('status', 'rejected')->count();

        $recentPendingUploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->where('status', 'pending')
            ->orderBy('uploaded_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'recentPendingUploads'
        ));
    }

    public function queue(Request $request)
    {
        $criteriaOptions = Kriteria::orderBy('code')->get(['id', 'code', 'title']);

        $uploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->where('status', 'pending')
            ->when($request->filled('upload_date'), function ($query) use ($request) {
                $query->whereDate('uploaded_at', $request->upload_date);
            })
            ->when($request->filled('criteria_id'), function ($query) use ($request) {
                $query->whereHas('maturityLevel.subkriteria', function ($subQuery) use ($request) {
                    $subQuery->where('criteria_id', $request->criteria_id);
                });
            })
            ->orderBy('uploaded_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.queue', compact('uploads', 'criteriaOptions'));
    }

    public function history(Request $request)
    {
        $criteriaOptions = Kriteria::orderBy('code')->get(['id', 'code', 'title']);

        $uploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->whereIn('status', ['approved', 'rejected'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('from_date'), function ($query) use ($request) {
                $query->whereDate('updated_at', '>=', $request->from_date);
            })
            ->when($request->filled('to_date'), function ($query) use ($request) {
                $query->whereDate('updated_at', '<=', $request->to_date);
            })
            ->when($request->filled('criteria_id'), function ($query) use ($request) {
                $query->whereHas('maturityLevel.subkriteria', function ($subQuery) use ($request) {
                    $subQuery->where('criteria_id', $request->criteria_id);
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.history', compact('uploads', 'criteriaOptions'));
    }

    public function verifyUpload(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'rejection_note' => 'required_if:status,rejected|nullable|string|max:1000',
        ]);

        $upload = EvidenceUpload::findOrFail($id);
        $upload->update([
            'status' => $request->status,
            'rejection_note' => $request->status === 'rejected' ? ($request->rejection_note ?? 'Dokumen tidak sesuai dengan persyaratan.') : null,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        ActivityLog::create([
            'evidence_upload_id' => $upload->id,
            'maturity_level_id' => $upload->maturity_level_id,
            'actor_id' => Auth::id(),
            'activity_type' => 'evaluation',
            'filename' => $upload->original_filename,
            'status' => $request->status,
            'occurred_at' => now(),
        ]);

        if ($request->status !== 'pending') {
            AppNotification::create([
                'recipient_id' => $upload->user_id,
                'type' => 'evaluation',
                'title' => 'Berkas telah dinilai',
                'message' => 'Verifikator telah menilai ' . $upload->original_filename . '. Status: ' . ($request->status === 'approved' ? 'Disetujui' : 'Perlu Revisi') . '.',
                'document_id' => $upload->id,
                'target_url' => route('user.kriteria', ['level' => $upload->maturity_level_id]) . '#level-' . $upload->maturity_level_id,
            ]);
        }

        $message = $request->status === 'approved'
            ? 'Dokumen berhasil disetujui.'
            : ($request->status === 'rejected'
                ? 'Dokumen berhasil ditolak dengan catatan alasan.'
                : 'Status dokumen dikembalikan ke pending.');

        return back()->with('success', $message);
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