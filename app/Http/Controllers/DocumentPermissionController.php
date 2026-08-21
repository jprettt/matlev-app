<?php

namespace App\Http\Controllers;

use App\Models\DocumentPermissionRequest;
use App\Models\EvidenceRevision;
use App\Models\EvidenceUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentPermissionController extends Controller
{
    public function request(Request $request, EvidenceUpload $upload)
    {
        $validated = $request->validate([
            'action' => 'required|in:edit,delete',
        ]);

        abort_if($upload->user_id === Auth::id(), 422, 'Pemilik dokumen tidak perlu meminta izin.');
        DocumentPermissionRequest::updateOrCreate(
            [
                'evidence_upload_id' => $upload->id,
                'requester_id' => Auth::id(),
                'action' => $validated['action'],
                'status' => 'pending',
            ],
            ['owner_id' => $upload->user_id]
        );

        return back()->with('success', 'Permintaan izin telah dikirim kepada pemilik dokumen.');
    }

    public function respond(Request $request, DocumentPermissionRequest $permissionRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        abort_unless($permissionRequest->owner_id === Auth::id(), 403);
        abort_unless($permissionRequest->status === 'pending', 422, 'Permintaan ini sudah diproses.');

        $permissionRequest->update([
            'status' => $validated['status'],
            'responded_at' => now(),
        ]);

        return back()->with('success', 'Permintaan izin telah ' . ($validated['status'] === 'approved' ? 'disetujui.' : 'ditolak.'));
    }

    public function update(Request $request, EvidenceUpload $upload)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:10240',
        ]);

        $permission = $this->usablePermission($upload, 'edit');
        abort_unless($upload->user_id === Auth::id() || $permission, 403, 'Anda belum mendapat izin edit dari pemilik dokumen.');

        $file = $request->file('pdf_file');
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
        $path = $file->storeAs('evidence_pdfs', $filename, 'public');
        $oldPath = $upload->file_path;

        DB::transaction(function () use ($upload, $path, $file, $permission) {
            $upload->update([
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'status' => 'pending',
                'rejection_note' => null,
                'uploaded_at' => now(),
            ]);

            if ($permission) {
                $permission->update(['used_at' => now()]);
            }
        });

        Storage::disk('public')->delete($oldPath);

        return back()->with('success', 'Dokumen berhasil diganti dan menunggu verifikasi ulang.');
    }

    public function destroy(EvidenceUpload $upload)
    {
        abort_if($upload->status === 'rejected', 422, 'Dokumen ditolak harus tetap tercatat sampai pemilik atau user mengirim revisi.');

        $permission = $this->usablePermission($upload, 'delete');
        abort_unless($upload->user_id === Auth::id() || $permission, 403, 'Anda belum mendapat izin hapus dari pemilik dokumen.');

        Storage::disk('public')->delete($upload->file_path);
        $upload->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function destroyRevision(EvidenceRevision $revision)
    {
        $upload = $revision->evidenceUpload;
        abort_unless(
            (int) $revision->user_id === (int) Auth::id()
                || (int) $upload->user_id === (int) Auth::id(),
            403,
            'Hanya pengunggah revisi atau pemilik dokumen yang dapat menghapus riwayat ini.'
        );

        abort_if($revision->status === 'deleted', 422, 'Riwayat revisi ini sudah dihapus.');
        abort_if($revision->is_current && $upload->status === 'rejected', 422, 'File aktif yang membutuhkan revisi tidak dapat dihapus dari riwayat.');

        Storage::disk('public')->delete($revision->file_path);
        $revision->update([
            'status' => 'deleted',
            'deleted_at' => now(),
            'deleted_by' => Auth::id(),
            'deletion_note' => 'File revisi dihapus oleh ' . Auth::user()->name,
        ]);

        if ($revision->is_current) {
            $previous = $upload->revisions()
                ->where('is_current', false)
                ->where('status', '!=', 'deleted')
                ->latest('version_number')
                ->first();

            if ($previous) {
                $previous->update(['is_current' => true]);
                $upload->update([
                    'user_id' => $previous->user_id,
                    'file_path' => $previous->file_path,
                    'original_filename' => $previous->original_filename,
                    'status' => 'rejected',
                    'rejection_note' => $previous->rejection_note ?? 'Dokumen perlu direvisi kembali.',
                    'uploaded_at' => $previous->uploaded_at,
                ]);
            }
        }

        return back()->with('success', 'File revisi dihapus dan tetap tercatat di riwayat.');
    }

    private function usablePermission(EvidenceUpload $upload, string $action): ?DocumentPermissionRequest
    {
        return DocumentPermissionRequest::where('evidence_upload_id', $upload->id)
            ->where('requester_id', Auth::id())
            ->where('action', $action)
            ->where('status', 'approved')
            ->whereNull('used_at')
            ->latest('responded_at')
            ->first();
    }
}
