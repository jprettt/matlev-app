<?php

namespace Tests\Feature;

use App\Models\DocumentPermissionRequest;
use App\Models\ActivityLog;
use App\Models\EvidenceUpload;
use App\Models\EvidenceRevision;
use App\Models\EvidenceRequirement;
use App\Models\EvidenceSlot;
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

    public function test_dashboard_percentage_counts_achieved_levels_even_without_evidence(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $criteria = Kriteria::create(['code' => 'K1', 'title' => 'Kriteria']);
        $subcriteria = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => 'K1.1', 'title' => 'Sub Kriteria']);

        MaturityLevel::create([
            'sub_criteria_id' => $subcriteria->id,
            'level' => 1,
            'level_number' => 1,
            'evidence_mode' => 'NONE',
            'evidence_requirement' => 'Tidak perlu bukti',
        ]);

        MaturityLevel::create([
            'sub_criteria_id' => $subcriteria->id,
            'level' => 2,
            'level_number' => 2,
            'evidence_mode' => 'REQUIRED',
            'evidence_requirement' => 'PDF',
        ]);

        $this->actingAs($user)
            ->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('50%');
    }

    public function test_dashboard_status_counts_approved_files_not_levels(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $criteria = Kriteria::create(['code' => 'K1', 'title' => 'Kriteria']);
        $subcriteria = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => 'K1.1', 'title' => 'Sub Kriteria']);
        $level = MaturityLevel::create(['sub_criteria_id' => $subcriteria->id, 'level' => 1, 'evidence_requirement' => 'PDF']);
        $requirement = EvidenceRequirement::create(['maturity_level_id' => $level->id, 'name' => 'Bukti', 'allowed_file_type' => 'pdf', 'max_file_size' => 10240]);
        $slot = EvidenceSlot::create(['evidence_requirement_id' => $requirement->id, 'name' => 'Slot', 'is_required' => true]);

        EvidenceUpload::create(['maturity_level_id' => $level->id, 'evidence_requirement_id' => $requirement->id, 'evidence_slot_id' => $slot->id, 'user_id' => $user->id, 'file_path' => 'a.pdf', 'original_filename' => 'a.pdf', 'status' => 'approved', 'uploaded_at' => now()]);
        EvidenceUpload::create(['maturity_level_id' => $level->id, 'evidence_requirement_id' => $requirement->id, 'evidence_slot_id' => $slot->id, 'user_id' => $user->id, 'file_path' => 'b.pdf', 'original_filename' => 'b.pdf', 'status' => 'approved', 'uploaded_at' => now()]);

        $this->actingAs($user)
            ->get(route('user.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn ($stats) => $stats['totalApproved'] === 2 && $stats['totalSlots'] === 1);
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

    public function test_verifier_redirect_keeps_selected_queue_tab_after_review(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $verifier = User::factory()->create(['role' => 'admin']);
        $criteria = Kriteria::create(['code' => 'K2', 'title' => 'Kriteria tambahan']);
        $subCriteria = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => 'K2.1', 'title' => 'Sub Kriteria tambahan']);
        $level = MaturityLevel::create(['sub_criteria_id' => $subCriteria->id, 'level' => 2, 'evidence_requirement' => 'PDF']);
        $upload = EvidenceUpload::create([
            'maturity_level_id' => $level->id,
            'user_id' => $owner->id,
            'file_path' => 'evidence_pdfs/selected-level.pdf',
            'original_filename' => 'selected-level.pdf',
            'status' => 'pending',
            'uploaded_at' => now(),
        ]);

        $this->actingAs($verifier)
            ->from(route('admin.queue', [
                'criteria_id' => $criteria->id,
                'sub_criteria_id' => $subCriteria->id,
                'level_id' => $level->id,
            ]))
            ->post(route('admin.verify', $upload), ['status' => 'approved'])
            ->assertRedirect(route('admin.queue', [
                'criteria_id' => $criteria->id,
                'sub_criteria_id' => $subCriteria->id,
                'level_id' => $level->id,
            ]));
    }

    public function test_verifier_can_review_next_level_when_previous_level_is_auto_fulfilled_without_upload(): void
    {
        $verifier = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $criteria = Kriteria::create(['code' => 'K3', 'title' => 'Kriteria auto']);
        $subCriteria = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => 'K3.1', 'title' => 'Sub Kriteria auto']);
        $levelOne = MaturityLevel::create(['sub_criteria_id' => $subCriteria->id, 'level' => 1, 'evidence_mode' => 'NONE', 'evidence_requirement' => 'Tidak perlu bukti']);
        $levelTwo = MaturityLevel::create(['sub_criteria_id' => $subCriteria->id, 'level' => 2, 'evidence_requirement' => 'PDF']);
        $upload = EvidenceUpload::create([
            'maturity_level_id' => $levelTwo->id,
            'user_id' => $owner->id,
            'file_path' => 'evidence_pdfs/level-2.pdf',
            'original_filename' => 'level-2.pdf',
            'status' => 'pending',
            'uploaded_at' => now(),
        ]);

        $this->actingAs($verifier)
            ->post(route('admin.verify', $upload), ['status' => 'approved'])
            ->assertSessionHas('success');
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

    public function test_pending_permission_is_invalidated_when_admin_reviews_file(): void
    {
        [$owner, $other, $upload] = $this->createDocumentFixture();
        $upload->update(['status' => 'pending']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($other)
            ->post(route('documents.permission.request', $upload), ['action' => 'edit'])
            ->assertSessionHas('success');
        $permission = DocumentPermissionRequest::firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.verify', $upload), ['status' => 'approved'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('document_permission_requests', [
            'id' => $permission->id,
            'status' => 'rejected',
        ]);
        $this->actingAs($owner)
            ->post(route('documents.permission.respond', $permission), ['status' => 'approved'])
            ->assertStatus(422);
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

    public function test_permission_approval_allows_requester_to_delete_file(): void
    {
        Storage::fake('public');
        [$owner, $other, $upload] = $this->createDocumentFixture();
        $upload->update(['status' => 'pending']);

        $this->actingAs($other)
            ->post(route('documents.permission.request', $upload), ['action' => 'edit'])
            ->assertSessionHas('success');

        $permission = DocumentPermissionRequest::firstOrFail();

        $this->actingAs($owner)
            ->post(route('documents.permission.respond', $permission), ['status' => 'approved'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('document_permission_requests', [
            'id' => $permission->id,
            'status' => 'approved',
            'requester_id' => $other->id,
            'owner_id' => $owner->id,
        ]);

        $this->actingAs($other)
            ->delete(route('documents.delete', $upload))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('evidence_uploads', ['id' => $upload->id]);
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
            ->assertSessionHas('success')
            ->assertRedirect(route('user.kriteria', ['level' => $levelOne->id]));

        $this->actingAs($user)
            ->post(route('matlev.upload', $levelTwo), ['pdf_file' => UploadedFile::fake()->create('level-2.pdf', 10, 'application/pdf')])
            ->assertSessionHas('success')
            ->assertRedirect(route('user.kriteria', ['level' => $levelTwo->id]));

        $this->assertDatabaseHas('evidence_uploads', [
            'maturity_level_id' => $levelTwo->id,
            'original_filename' => 'level-2.pdf',
        ]);
    }

    public function test_upload_redirect_keeps_selected_level_after_refresh(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'user']);
        $criteria = Kriteria::create(['code' => 'K1', 'title' => 'Kriteria']);
        $subcriteria = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => 'K1.1', 'title' => 'Sub Kriteria']);

        $levelOne = MaturityLevel::create(['sub_criteria_id' => $subcriteria->id, 'level' => 1, 'evidence_mode' => 'REQUIRED']);
        $levelTwo = MaturityLevel::create(['sub_criteria_id' => $subcriteria->id, 'level' => 2, 'evidence_mode' => 'REQUIRED']);

        $requirementOne = EvidenceRequirement::create(['maturity_level_id' => $levelOne->id, 'name' => 'Bukti Level 1', 'allowed_file_type' => 'pdf', 'max_file_size' => 10240]);
        $slotOne = EvidenceSlot::create(['evidence_requirement_id' => $requirementOne->id, 'name' => 'Slot Level 1', 'is_required' => true]);

        $this->actingAs($user)
            ->post(route('evidence.slot.upload', $slotOne), ['document' => UploadedFile::fake()->create('level-1.pdf', 10, 'application/pdf')])
            ->assertSessionHas('success');

        $requirementTwo = EvidenceRequirement::create(['maturity_level_id' => $levelTwo->id, 'name' => 'Bukti Level 2', 'allowed_file_type' => 'pdf', 'max_file_size' => 10240]);
        $slotTwo = EvidenceSlot::create(['evidence_requirement_id' => $requirementTwo->id, 'name' => 'Slot Level 2', 'is_required' => true]);

        $this->actingAs($user)
            ->post(route('evidence.slot.upload', $slotTwo), ['document' => UploadedFile::fake()->create('level-2.pdf', 10, 'application/pdf')])
            ->assertSessionHas('success')
            ->assertRedirect(route('user.kriteria', ['criteria_id' => $criteria->id, 'level' => $levelTwo->id, 'requirement' => $requirementTwo->id]));
    }

    public function test_criteria_score_is_average_of_subcriteria_scores(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $criteria = Kriteria::create(['code' => 'K1', 'title' => 'Kriteria']);

        foreach (['K1.1', 'K1.2', 'K1.3'] as $code) {
            $sub = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => $code, 'title' => 'Sub ' . $code]);
            $level = MaturityLevel::create(['sub_criteria_id' => $sub->id, 'level' => 1, 'evidence_mode' => 'REQUIRED']);
            $requirement = EvidenceRequirement::create(['maturity_level_id' => $level->id, 'name' => 'Bukti', 'allowed_file_type' => 'pdf', 'max_file_size' => 10240]);
            $slot = EvidenceSlot::create(['evidence_requirement_id' => $requirement->id, 'name' => 'Slot', 'is_required' => true]);
            EvidenceUpload::create(['maturity_level_id' => $level->id, 'evidence_requirement_id' => $requirement->id, 'evidence_slot_id' => $slot->id, 'user_id' => $user->id, 'file_path' => 'proof.pdf', 'original_filename' => 'proof.pdf', 'status' => 'approved', 'uploaded_at' => now()]);
        }

        $this->assertSame(1, $criteria->fresh()->subKriterias->first()->scoreForUser($user->id));
        $this->assertSame(1.0, $criteria->fresh()->scoreForUser($user->id));
    }

    public function test_scores_use_highest_completed_level_and_criteria_average(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $criteria = Kriteria::create(['code' => 'K1', 'title' => 'Kriteria']);
        $subOne = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => 'K1.1', 'title' => 'Sub Satu']);
        $subTwo = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => 'K1.2', 'title' => 'Sub Dua']);

        foreach ([$subOne, $subTwo] as $sub) {
            $level = MaturityLevel::create(['sub_criteria_id' => $sub->id, 'level' => 3, 'evidence_mode' => 'REQUIRED']);
            $requirement = EvidenceRequirement::create(['maturity_level_id' => $level->id, 'name' => 'Bukti', 'allowed_file_type' => 'pdf', 'max_file_size' => 10240]);
            $slot = EvidenceSlot::create(['evidence_requirement_id' => $requirement->id, 'name' => 'Slot', 'is_required' => true]);
            EvidenceUpload::create(['maturity_level_id' => $level->id, 'evidence_requirement_id' => $requirement->id, 'evidence_slot_id' => $slot->id, 'user_id' => $user->id, 'file_path' => 'proof.pdf', 'original_filename' => 'proof.pdf', 'status' => 'approved', 'uploaded_at' => now()]);
        }

        $this->assertSame(3, $subOne->fresh()->scoreForUser($user->id));
        $this->assertSame(3.0, $criteria->fresh()->scoreForUser($user->id));
    }

    public function test_previous_level_upload_is_shared_between_users(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create(['role' => 'user']);
        $requester = User::factory()->create(['role' => 'user']);
        $criteria = Kriteria::create(['code' => 'K1', 'title' => 'Kriteria']);
        $subcriteria = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => 'K1.1', 'title' => 'Sub Kriteria']);
        $levelOne = MaturityLevel::create(['sub_criteria_id' => $subcriteria->id, 'level' => 1, 'evidence_requirement' => 'PDF']);
        $levelTwo = MaturityLevel::create(['sub_criteria_id' => $subcriteria->id, 'level' => 2, 'evidence_requirement' => 'PDF']);
        EvidenceUpload::create(['maturity_level_id' => $levelOne->id, 'user_id' => $owner->id, 'file_path' => 'owner.pdf', 'original_filename' => 'owner.pdf', 'status' => 'approved', 'uploaded_at' => now()]);

        $this->actingAs($requester)
            ->post(route('matlev.upload', $levelTwo), ['pdf_file' => UploadedFile::fake()->create('level-2.pdf', 10, 'application/pdf')])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('evidence_uploads', [
            'maturity_level_id' => $levelTwo->id,
            'user_id' => $requester->id,
            'original_filename' => 'level-2.pdf',
        ]);
    }

    public function test_pending_evidence_does_not_increase_subcriteria_score(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $criteria = Kriteria::create(['code' => 'K1', 'title' => 'Kriteria']);
        $subcriteria = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => 'K1.1', 'title' => 'Sub Kriteria']);
        $level = MaturityLevel::create(['sub_criteria_id' => $subcriteria->id, 'level' => 2, 'evidence_requirement' => 'PDF']);
        EvidenceUpload::create([
            'maturity_level_id' => $level->id,
            'user_id' => $user->id,
            'file_path' => 'evidence_pdfs/pending.pdf',
            'original_filename' => 'pending.pdf',
            'status' => 'pending',
            'uploaded_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('user.kriteria', ['level' => $level->id]));

        $response->assertOk();
        $this->assertMatchesRegularExpression('/Nilai SK:.*?font-extrabold[^>]*>\s*0\s*</s', $response->getContent());
    }

    public function test_level_score_stays_zero_when_one_of_multiple_files_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'user']);
        $criteria = Kriteria::create(['code' => 'K1', 'title' => 'Kriteria']);
        $subcriteria = Subkriteria::create(['criteria_id' => $criteria->id, 'code' => 'K1.1', 'title' => 'Sub Kriteria']);
        $level = MaturityLevel::create(['sub_criteria_id' => $subcriteria->id, 'level' => 1, 'evidence_requirement' => 'PDF']);

        $response = $this->actingAs($user)->post(route('matlev.upload', $level), [
            'pdf_files' => [
                UploadedFile::fake()->create('one.pdf', 10, 'application/pdf'),
                UploadedFile::fake()->create('two.pdf', 10, 'application/pdf'),
                UploadedFile::fake()->create('three.pdf', 10, 'application/pdf'),
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('evidence_uploads', 3);

        EvidenceUpload::query()->where('original_filename', 'two.pdf')->update(['status' => 'rejected']);

        $page = $this->actingAs($user)->get(route('user.kriteria', ['level' => $level->id]));

        $page->assertOk();
        $this->assertMatchesRegularExpression('/Nilai SK:.*?font-extrabold[^>]*>\s*0\s*</s', $page->getContent());
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
