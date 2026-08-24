<?php

namespace App\Http\Controllers;

use App\Models\EvidenceUpload;
use App\Models\ActivityLog;
use App\Models\Kriteria;
use App\Models\MaturityLevel;
use App\Models\Subkriteria;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $pendingCount = EvidenceUpload::where('status', 'pending')->count();
        $approvedCount = EvidenceUpload::where('status', 'approved')->count();
        $rejectedCount = EvidenceUpload::where('status', 'rejected')->count();

        $completedUploads = EvidenceUpload::whereIn('status', ['approved', 'rejected'])
            ->whereNotNull('uploaded_at')
            ->get(['uploaded_at', 'updated_at']);

        $avgSlaHours = round($completedUploads->avg(function ($upload) {
            $uploadedAt = $upload->uploaded_at instanceof Carbon ? $upload->uploaded_at : Carbon::parse($upload->uploaded_at);
            $completedAt = $upload->updated_at instanceof Carbon ? $upload->updated_at : Carbon::parse($upload->updated_at);
            return max(0, $uploadedAt->diffInMinutes($completedAt) / 60);
        }) ?? 0, 1);

        $recentPendingUploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->where('status', 'pending')
            ->orderBy('uploaded_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'avgSlaHours',
            'recentPendingUploads'
        ));
    }

    public function queue(Request $request)
    {
        $units = User::query()
            ->whereNotNull('unit_kerja')
            ->where('unit_kerja', '!=', '')
            ->orderBy('unit_kerja')
            ->distinct()
            ->pluck('unit_kerja');

        $criteriaOptions = Kriteria::orderBy('code')->get(['id', 'code', 'title']);

        $uploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->where('status', 'pending')
            ->when($request->filled('unit_kerja'), function ($query) use ($request) {
                $query->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery->where('unit_kerja', $request->unit_kerja);
                });
            })
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

        return view('admin.queue', compact('uploads', 'units', 'criteriaOptions'));
    }

    public function history(Request $request)
    {
        $units = User::query()
            ->whereNotNull('unit_kerja')
            ->where('unit_kerja', '!=', '')
            ->orderBy('unit_kerja')
            ->distinct()
            ->pluck('unit_kerja');

        $criteriaOptions = Kriteria::orderBy('code')->get(['id', 'code', 'title']);

        $uploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->whereIn('status', ['approved', 'rejected'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('unit_kerja'), function ($query) use ($request) {
                $query->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery->where('unit_kerja', $request->unit_kerja);
                });
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

        return view('admin.history', compact('uploads', 'units', 'criteriaOptions'));
    }

    public function users()
    {
        $users = User::orderBy('name')->get();

        return view('admin.users', compact('users'));
    }

    public function updateUserRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:user,admin,atasan',
        ]);

        $user = User::findOrFail($id);
        $user->update(['role' => $request->role]);

        return back()->with('success', 'Hak akses pengguna berhasil diperbarui.');
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password pengguna berhasil direset.');
    }

    public function verifyUpload(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'rejection_note' => 'nullable|string|max:1000',
        ]);

        $upload = EvidenceUpload::findOrFail($id);
        $upload->update([
            'status' => $request->status,
            'rejection_note' => $request->status === 'rejected' ? ($request->rejection_note ?? 'Dokumen tidak sesuai dengan persyaratan.') : null,
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