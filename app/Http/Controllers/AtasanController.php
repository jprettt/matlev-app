<?php

namespace App\Http\Controllers;

use App\Models\EvidenceUpload;
use App\Models\ActivityLog;
use App\Models\Kriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

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

    public function statusSummary()
    {
        $uploads = EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->orderByDesc('uploaded_at')
            ->get();

        return view('atasan.status-summary', compact('uploads'));
    }

    public function activityHistory(Request $request)
    {
        $activityTypes = collect(['login', 'logout', 'upload', 'revision_upload', 'evaluation', 'verification_opened']);
        $users = User::query()->orderBy('name')->get(['id', 'name', 'role']);
        $activityLogs = ActivityLog::with(['actor', 'targetUser', 'maturityLevel.subkriteria.kriteria'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(function ($logQuery) use ($search) {
                    $logQuery->where('filename', 'like', '%' . $search . '%')
                        ->orWhere('note', 'like', '%' . $search . '%')
                        ->orWhereHas('actor', fn ($actorQuery) => $actorQuery->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('evidenceUpload', fn ($uploadQuery) => $uploadQuery->where('original_filename', 'like', '%' . $search . '%'));
                });
            })
            ->when($request->filled('user_id'), fn ($query) => $query->where('actor_id', $request->user_id))
            ->when($request->filled('role'), fn ($query) => $query->whereHas('actor', fn ($actorQuery) => $actorQuery->where('role', $request->role)))
            ->when($request->filled('activity_type'), fn ($query) => $query->where('activity_type', $request->activity_type))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('occurred_at', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('occurred_at', '<=', $request->to_date))
            ->orderByDesc('occurred_at')
            ->paginate(20)
            ->withQueryString();

        return view('atasan.activity', compact('activityLogs', 'activityTypes', 'users'));
    }

    public function users()
    {
        $users = User::query()->orderBy('name')->get();

        return view('atasan.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['user', 'admin', 'atasan'])],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;
        User::create($validated);

        return back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['user', 'admin', 'atasan'])],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);
        if (! empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return back()->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleUser(User $user)
    {
        abort_if($user->id === Auth::id(), 422, 'Akun Anda sendiri tidak dapat dinonaktifkan.');
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Status akun berhasil diperbarui.');
    }

    public function exportForm()
    {
        return view('atasan.export');
    }

    public function exportSummary(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);
        $uploads = $this->uploadsForExport($validated);

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

    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);
        $uploads = $this->uploadsForExport($validated);

        return view('atasan.export-pdf', compact('uploads', 'validated'));
    }

    public function exportExcel(Request $request)
    {
        return $this->exportSummary($request);
    }

    private function uploadsForExport(array $dates)
    {
        return EvidenceUpload::with(['user', 'maturityLevel.subkriteria.kriteria'])
            ->when($dates['from_date'] ?? null, fn ($query, $date) => $query->whereDate('uploaded_at', '>=', $date))
            ->when($dates['to_date'] ?? null, fn ($query, $date) => $query->whereDate('uploaded_at', '<=', $date))
            ->orderByDesc('uploaded_at')
            ->get();
    }
}
