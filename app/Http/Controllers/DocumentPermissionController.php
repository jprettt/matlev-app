<?php

namespace App\Http\Controllers;

use App\Models\DocumentPermissionRequest;
use App\Models\EvidenceRevision;
use App\Models\EvidenceUpload;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentPermissionController extends Controller
{
    public function request(Request $request, EvidenceUpload $upload)
    {
        $validated = $request->validate([
            'action' => 'required|in:edit',
            'reason' => 'nullable|string|max:1000',
        ]);

        abort_if($upload->user_id === Auth::id(), 422, 'Pemilik dokumen tidak perlu meminta izin.');
        abort_if($upload->status !== 'pending', 422, 'Dokumen yang sudah dinilai tidak memerlukan permintaan izin mengganti.');
        $permissionRequest = DocumentPermissionRequest::updateOrCreate(
            [
                'evidence_upload_id' => $upload->id,
                'requester_id' => Auth::id(),
                'action' => $validated['action'],
                'status' => 'pending',
            ],
            ['owner_id' => $upload->user_id, 'reason' => $validated['reason'] ?? null]
        );

        ActivityLog::create([
            'evidence_upload_id' => $upload->id,
            'maturity_level_id' => $upload->maturity_level_id,
            'actor_id' => Auth::id(),
            'target_user_id' => $upload->user_id,
            'activity_type' => 'permission_request',
            'filename' => $upload->original_filename,
            'status' => $permissionRequest->status,
            'occurred_at' => now(),
        ]);

        $requirementId = $upload->evidence_requirement_id ?? $upload->evidenceRequirement?->id;
        $criteriaId = $upload->maturityLevel?->subkriteria?->kriteria?->id ?? null;
        $targetUrl = $requirementId
            ? route('user.kriteria', ['level' => $upload->maturity_level_id, 'requirement' => $requirementId, 'criteria_id' => $criteriaId]) . '#requirement-' . $requirementId
            : route('user.kriteria', ['level' => $upload->maturity_level_id, 'criteria_id' => $criteriaId]) . '#level-' . $upload->maturity_level_id;

        AppNotification::create([
            'recipient_id' => $upload->user_id,
            'type' => 'permission_request',
            'title' => 'Permintaan izin dokumen',
            'message' => Auth::user()->name . ' meminta izin mengganti ' . $upload->original_filename . ($permissionRequest->reason ? ': ' . $permissionRequest->reason : '.'),
            'document_id' => $upload->id,
            'request_id' => $permissionRequest->id,
            'target_url' => $targetUrl,
        ]);

        return back()->with('success', 'Permintaan izin telah dikirim kepada pemilik dokumen.');
    }

    public function respond(Request $request, DocumentPermissionRequest $permissionRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        abort_unless($permissionRequest->owner_id === Auth::id(), 403);
        abort_unless($permissionRequest->status === 'pending', 422, 'Permintaan ini sudah diproses.');
        abort_if($permissionRequest->evidenceUpload->status !== 'pending', 422, 'Dokumen sudah dinilai sehingga permintaan izin tidak berlaku lagi.');

        $permissionRequest->update([
            'status' => $validated['status'],
            'responded_at' => now(),
        ]);

        ActivityLog::create([
            'evidence_upload_id' => $permissionRequest->evidence_upload_id,
            'maturity_level_id' => $permissionRequest->evidenceUpload->maturity_level_id,
            'actor_id' => Auth::id(),
            'target_user_id' => $permissionRequest->requester_id,
            'activity_type' => $validated['status'] === 'approved' ? 'permission_granted' : 'permission_rejected',
            'filename' => $permissionRequest->evidenceUpload->original_filename,
            'status' => $validated['status'],
            'occurred_at' => $permissionRequest->responded_at,
        ]);

        $requirementId = $permissionRequest->evidenceUpload->evidence_requirement_id ?? $permissionRequest->evidenceUpload->evidenceRequirement?->id;
        $criteriaId = $permissionRequest->evidenceUpload->maturityLevel?->subkriteria?->kriteria?->id ?? null;
        $targetUrl = $requirementId
            ? route('user.kriteria', ['level' => $permissionRequest->evidenceUpload->maturity_level_id, 'requirement' => $requirementId, 'criteria_id' => $criteriaId]) . '#requirement-' . $requirementId
            : route('user.kriteria', ['level' => $permissionRequest->evidenceUpload->maturity_level_id, 'criteria_id' => $criteriaId]) . '#level-' . $permissionRequest->evidenceUpload->maturity_level_id;

        AppNotification::create([
            'recipient_id' => $permissionRequest->requester_id,
            'type' => 'permission_' . $validated['status'],
            'title' => 'Permintaan izin ' . ($validated['status'] === 'approved' ? 'disetujui' : 'ditolak'),
            'message' => Auth::user()->name . ' telah ' . ($validated['status'] === 'approved' ? 'menyetujui' : 'menolak') . ' permintaan Anda untuk mengganti ' . $permissionRequest->evidenceUpload->original_filename . '.',
            'document_id' => $permissionRequest->evidence_upload_id,
            'request_id' => $permissionRequest->id,
            'target_url' => $targetUrl,
        ]);

        return back()->with('success', 'Permintaan izin telah ' . ($validated['status'] === 'approved' ? 'disetujui.' : 'ditolak.'));
    }

    public function destroy(EvidenceUpload $upload)
    {
        abort_if($upload->status === 'approved', 422, 'Dokumen yang sudah disetujui tidak dapat dihapus.');

        $permission = $this->usablePermission($upload, 'edit');
        abort_unless($upload->user_id === Auth::id() || $permission, 403, 'Anda belum mendapat izin untuk menghapus dokumen ini.');

        $maturityLevelId = $upload->maturity_level_id;
        $evidenceRequirementId = $upload->evidence_requirement_id ?? $upload->evidenceRequirement?->id;

        Storage::disk('public')->delete($upload->file_path);
        ActivityLog::create([
            'evidence_upload_id' => $upload->id,
            'maturity_level_id' => $upload->maturity_level_id,
            'actor_id' => Auth::id(),
            'activity_type' => 'delete',
            'filename' => $upload->original_filename,
            'occurred_at' => now(),
        ]);
        if ($permission) {
            $permission->update(['used_at' => now()]);
        }
        $upload->delete();

        // Redirect ke halaman kriteria yang sama (tetap di level yang sama)
        $redirectUrl = $evidenceRequirementId
            ? route('user.kriteria', ['level' => $maturityLevelId, 'requirement' => $evidenceRequirementId]) . '#requirement-' . $evidenceRequirementId
            : route('user.kriteria', ['level' => $maturityLevelId]) . '#level-' . $maturityLevelId;

        return redirect($redirectUrl)->with('success', 'Dokumen berhasil dihapus.');
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

        $maturityLevelId = $upload->maturity_level_id;
        $evidenceRequirementId = $upload->evidence_requirement_id ?? $upload->evidenceRequirement?->id;

        Storage::disk('public')->delete($revision->file_path);
        ActivityLog::create([
            'evidence_upload_id' => $upload->id,
            'maturity_level_id' => $upload->maturity_level_id,
            'actor_id' => Auth::id(),
            'activity_type' => 'delete',
            'filename' => $revision->original_filename,
            'occurred_at' => now(),
        ]);
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

        // Redirect ke halaman kriteria yang sama (tetap di level yang sama)
        $redirectUrl = $evidenceRequirementId
            ? route('user.kriteria', ['level' => $maturityLevelId, 'requirement' => $evidenceRequirementId]) . '#requirement-' . $evidenceRequirementId
            : route('user.kriteria', ['level' => $maturityLevelId]) . '#level-' . $maturityLevelId;

        return redirect($redirectUrl)->with('success', 'File revisi dihapus dan tetap tercatat di riwayat.');
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
