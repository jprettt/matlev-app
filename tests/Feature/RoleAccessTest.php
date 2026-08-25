<?php

namespace Tests\Feature;

use App\Models\DocumentPermissionRequest;
use App\Models\ActivityLog;
use App\Models\EvidenceUpload;
use App\Models\EvidenceRevision;
use App\Models\Kriteria;
use App\Models\MaturityLevel;
use App\Models\Subkriteria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_based_accounts_are_available(): void
    {
        User::factory()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@matlev.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'User Demo',
            'email' => 'user@matlev.test',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        User::factory()->create([
            'name' => 'Atasan Demo',
            'email' => 'atasan@matlev.test',
            'password' => Hash::make('password123'),
            'role' => 'atasan',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'admin@matlev.test', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'user@matlev.test', 'role' => 'user']);
        $this->assertDatabaseHas('users', ['email' => 'atasan@matlev.test', 'role' => 'atasan']);
    }

    public function test_verifier_evaluation_is_added_to_activity_log(): void
    {
        [$owner, $other, $upload] = $this->createDocumentFixture();
        $verifier = User::factory()->create(['role' => 'admin', 'name' => 'Verifier Test']);
        $upload->update(['status' => 'pending']);

        $this->actingAs($verifier)
            ->post(route('admin.verify', $upload), ['status' => 'approved'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('activity_logs', [
            'evidence_upload_id' => $upload->id,
            'actor_id' => $verifier->id,
            'activity_type' => 'evaluation',
            'status' => 'approved',
        ]);

        $this->actingAs($owner)
            ->get(route('user.history'))
            ->assertOk()
            ->assertSee('Verifikator menyetujui file')
            ->assertSee('original.pdf')
            ->assertSee('Menghapus')
            ->assertSee('Saya')
            ->assertSee('Tim')
            ->assertSee('Semua Aktor')
            ->assertSee('Semua Aktivitas');
    }

    public function test_other_user_cannot_delete_until_owner_approves(): void
    {
        Storage::fake('public');
        [$owner, $other, $upload] = $this->createDocumentFixture();
        $upload->update(['status' => 'pending']);

        $this->actingAs($other)
            ->delete(route('documents.delete', $upload))
            ->assertForbidden();

        $this->actingAs($other)
            ->post(route('documents.permission.request', $upload), ['action' => 'edit'])
            ->assertSessionHas('success');

        $permission = DocumentPermissionRequest::firstOrFail();
        $this->actingAs($owner)
            ->post(route('documents.permission.respond', $permission), ['status' => 'approved'])
            ->assertSessionHas('success');

        $this->actingAs($other)
            ->delete(route('documents.delete', $upload))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('evidence_uploads', ['id' => $upload->id]);
        $this->assertDatabaseHas('activity_logs', [
            'maturity_level_id' => $upload->maturity_level_id,
            'actor_id' => $other->id,
            'activity_type' => 'delete',
            'filename' => 'original.pdf',
        ]);
    }

    public function test_other_user_cannot_request_edit_for_approved_document(): void
    {
        [$owner, $other, $upload] = $this->createDocumentFixture();

        $this->actingAs($owner)
            ->delete(route('documents.delete', $upload))
            ->assertStatus(422);

        $this->actingAs($other)
            ->post(route('documents.permission.request', $upload), ['action' => 'edit'])
            ->assertStatus(422);

        $this->assertDatabaseMissing('document_permission_requests', [
            'evidence_upload_id' => $upload->id,
            'requester_id' => $other->id,
            'action' => 'edit',
        ]);
    }

    public function test_other_user_cannot_request_delete_permission_or_delete_document(): void
    {
        Storage::fake('public');
        [$owner, $other, $upload] = $this->createDocumentFixture();
        $upload->update(['status' => 'pending']);

        $this->actingAs($other)
            ->delete(route('documents.delete', $upload))
            ->assertForbidden();

        $this->actingAs($other)
            ->post(route('documents.permission.request', $upload), ['action' => 'delete'])
            ->assertSessionHasErrors('action');

        $this->assertDatabaseMissing('document_permission_requests', [
            'evidence_upload_id' => $upload->id,
            'action' => 'delete',
        ]);
    }

    public function test_user_must_upload_previous_level_first(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'user']);
        $criteria = Kriteria::create(['code' => 'K1', 'title' => 'Kriteria']);
        $subcriteria = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => 'K1.1', 'title' => 'Sub Kriteria']);
        $levelOne = MaturityLevel::create(['sub_criteria_id' => $subcriteria->id, 'level' => 1, 'evidence_requirement' => 'PDF']);
        $levelTwo = MaturityLevel::create(['sub_criteria_id' => $subcriteria->id, 'level' => 2, 'evidence_requirement' => 'PDF']);

        $this->actingAs($user)
            ->post(route('matlev.upload', $levelTwo), ['pdf_file' => UploadedFile::fake()->create('level-2.pdf', 10, 'application/pdf')])
            ->assertSessionHas('error', 'Anda wajib mengunggah dokumen Level 1 terlebih dahulu.');

        $this->actingAs($user)
            ->post(route('matlev.upload', $levelOne), ['pdf_file' => UploadedFile::fake()->create('level-1.pdf', 10, 'application/pdf')])
            ->assertSessionHas('success');

        $this->actingAs($user)
            ->post(route('matlev.upload', $levelTwo), ['pdf_file' => UploadedFile::fake()->create('level-2.pdf', 10, 'application/pdf')])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('evidence_uploads', [
            'maturity_level_id' => $levelTwo->id,
            'original_filename' => 'level-2.pdf',
        ]);
    }

    public function test_any_user_can_submit_rejected_revision_and_old_file_is_kept(): void
    {
        Storage::fake('public');
        [$owner, $other, $upload] = $this->createDocumentFixture();
        $upload->update(['status' => 'rejected', 'rejection_note' => 'Perlu diperbaiki.']);

        $this->actingAs($other)
            ->post(route('matlev.upload', $upload->maturity_level_id), ['pdf_file' => UploadedFile::fake()->create('other-revision.pdf', 10, 'application/pdf')])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('evidence_uploads', [
            'id' => $upload->id,
            'user_id' => $other->id,
            'original_filename' => 'other-revision.pdf',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('evidence_revisions', [
            'evidence_upload_id' => $upload->id,
            'original_filename' => 'original.pdf',
            'status' => 'rejected',
        ]);
    }

    public function test_deleting_active_revision_restores_rejected_level_and_keeps_audit(): void
    {
        Storage::fake('public');
        [$owner, $other, $upload] = $this->createDocumentFixture();
        $upload->update(['status' => 'rejected', 'rejection_note' => 'Perlu diperbaiki.']);

        $this->actingAs($other)->post(route('matlev.upload', $upload->maturity_level_id), [
            'pdf_file' => UploadedFile::fake()->create('other-revision.pdf', 10, 'application/pdf'),
        ]);
        $activeRevision = EvidenceRevision::where('evidence_upload_id', $upload->id)->where('is_current', true)->firstOrFail();

        $this->actingAs($other)
            ->delete(route('documents.revisions.delete', $activeRevision))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('evidence_uploads', [
            'id' => $upload->id,
            'original_filename' => 'original.pdf',
            'status' => 'rejected',
        ]);
        $this->assertDatabaseHas('evidence_revisions', [
            'id' => $activeRevision->id,
            'status' => 'deleted',
            'deleted_by' => $other->id,
        ]);
    }

    public function test_history_does_not_duplicate_rejected_file_after_revision_upload(): void
    {
        Storage::fake('public');
        [$owner, $other, $upload] = $this->createDocumentFixture();
        $upload->update(['status' => 'rejected', 'rejection_note' => 'Perlu diperbaiki.']);

        $this->actingAs($other)->post(route('matlev.upload', $upload->maturity_level_id), [
            'pdf_file' => UploadedFile::fake()->create('revision-level-1.pdf', 10, 'application/pdf'),
        ]);

        $response = $this->actingAs($owner)->get(route('user.history'));

        $response->assertOk()
            ->assertSee('revision-level-1.pdf')
            ->assertDontSee('original.pdf');
    }

    private function createDocumentFixture(): array
    {
        $owner = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);
        $criteria = Kriteria::create(['code' => 'K1', 'title' => 'Kriteria']);
        $subcriteria = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => 'K1.1', 'title' => 'Sub Kriteria']);
        $level = MaturityLevel::create(['sub_criteria_id' => $subcriteria->id, 'level' => 1, 'evidence_requirement' => 'PDF']);
        $upload = EvidenceUpload::create([
            'maturity_level_id' => $level->id,
            'user_id' => $owner->id,
            'file_path' => 'evidence_pdfs/original.pdf',
            'original_filename' => 'original.pdf',
            'status' => 'approved',
            'uploaded_at' => now(),
        ]);

        return [$owner, $other, $upload];
    }
}
