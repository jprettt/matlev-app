<?php

namespace Tests\Feature;

use App\Models\DocumentPermissionRequest;
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

    public function test_other_user_cannot_edit_until_owner_approves(): void
    {
        Storage::fake('public');
        [$owner, $other, $upload] = $this->createDocumentFixture();

        $this->actingAs($other)
            ->post(route('documents.edit', $upload), ['pdf_file' => UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf')])
            ->assertForbidden();

        $this->actingAs($other)
            ->post(route('documents.permission.request', $upload), ['action' => 'edit'])
            ->assertSessionHas('success');

        $permission = DocumentPermissionRequest::firstOrFail();
        $this->actingAs($owner)
            ->post(route('documents.permission.respond', $permission), ['status' => 'approved'])
            ->assertSessionHas('success');

        $this->actingAs($other)
            ->post(route('documents.edit', $upload), ['pdf_file' => UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf')])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('evidence_uploads', [
            'id' => $upload->id,
            'original_filename' => 'replacement.pdf',
            'status' => 'pending',
        ]);
        $this->assertNotNull($permission->fresh()->used_at);
    }

    public function test_other_user_cannot_delete_until_owner_approves(): void
    {
        Storage::fake('public');
        [$owner, $other, $upload] = $this->createDocumentFixture();

        $this->actingAs($other)
            ->delete(route('documents.delete', $upload))
            ->assertForbidden();

        $this->actingAs($other)
            ->post(route('documents.permission.request', $upload), ['action' => 'delete']);

        $permission = DocumentPermissionRequest::firstOrFail();
        $this->actingAs($owner)
            ->post(route('documents.permission.respond', $permission), ['status' => 'approved']);

        $this->actingAs($other)
            ->delete(route('documents.delete', $upload))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('evidence_uploads', ['id' => $upload->id]);
        $this->assertDatabaseMissing('document_permission_requests', ['id' => $permission->id]);
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

    public function test_history_keeps_rejected_original_and_revision_as_separate_entries(): void
    {
        Storage::fake('public');
        [$owner, $other, $upload] = $this->createDocumentFixture();
        $upload->update(['status' => 'rejected', 'rejection_note' => 'Perlu diperbaiki.']);

        $this->actingAs($other)->post(route('matlev.upload', $upload->maturity_level_id), [
            'pdf_file' => UploadedFile::fake()->create('revision-level-1.pdf', 10, 'application/pdf'),
        ]);

        $response = $this->actingAs($owner)->get(route('user.history'));

        $response->assertOk()
            ->assertSee('original.pdf')
            ->assertSee('revision-level-1.pdf')
            ->assertSee('Ditolak / Perlu Revisi')
            ->assertSee('revision-level-1.pdf');
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
