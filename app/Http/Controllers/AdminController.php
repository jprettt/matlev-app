<?php

namespace App\Http\Controllers;

use App\Models\EvidenceUpload;
use App\Models\Kriteria;
use App\Models\MaturityLevel;
use App\Models\Subkriteria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $status = $request->get('status', 'all');

        $uploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderByDesc('uploaded_at')
            ->get();

        $criteria = Kriteria::with(['subKriterias.maturityLevels'])->get();

        return view('admin.dashboard', compact('uploads', 'criteria'));
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