<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_upload_id')->nullable()->constrained('evidence_uploads')->nullOnDelete();
            $table->foreignId('maturity_level_id')->nullable()->constrained('maturity_levels')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activity_type');
            $table->string('filename')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['activity_type', 'occurred_at']);
            $table->index(['actor_id', 'occurred_at']);
        });

        foreach (DB::table('evidence_uploads')->get() as $upload) {
            DB::table('activity_logs')->insert([
                'evidence_upload_id' => $upload->id,
                'maturity_level_id' => $upload->maturity_level_id,
                'actor_id' => $upload->user_id,
                'activity_type' => 'upload',
                'filename' => $upload->original_filename,
                'status' => $upload->status,
                'occurred_at' => $upload->uploaded_at ?? $upload->created_at,
                'created_at' => $upload->created_at,
                'updated_at' => $upload->updated_at,
            ]);
        }

        foreach (DB::table('evidence_revisions')->get() as $revision) {
            $maturityLevelId = DB::table('evidence_uploads')
                ->where('id', $revision->evidence_upload_id)
                ->value('maturity_level_id');

            DB::table('activity_logs')->insert([
                'evidence_upload_id' => $revision->evidence_upload_id,
                'maturity_level_id' => $maturityLevelId,
                'actor_id' => $revision->user_id,
                'activity_type' => 'revision_upload',
                'filename' => $revision->original_filename,
                'status' => $revision->status,
                'occurred_at' => $revision->uploaded_at ?? $revision->created_at,
                'created_at' => $revision->created_at,
                'updated_at' => $revision->updated_at,
            ]);
        }

        foreach (DB::table('document_permission_requests')->get() as $permission) {
            $maturityLevelId = DB::table('evidence_uploads')
                ->where('id', $permission->evidence_upload_id)
                ->value('maturity_level_id');
            $filename = DB::table('evidence_uploads')
                ->where('id', $permission->evidence_upload_id)
                ->value('original_filename');

            DB::table('activity_logs')->insert([
                'evidence_upload_id' => $permission->evidence_upload_id,
                'maturity_level_id' => $maturityLevelId,
                'actor_id' => $permission->requester_id,
                'target_user_id' => $permission->owner_id,
                'activity_type' => 'permission_request',
                'filename' => $filename,
                'status' => 'pending',
                'occurred_at' => $permission->created_at,
                'created_at' => $permission->created_at,
                'updated_at' => $permission->updated_at,
            ]);

            if ($permission->responded_at) {
                DB::table('activity_logs')->insert([
                    'evidence_upload_id' => $permission->evidence_upload_id,
                    'maturity_level_id' => $maturityLevelId,
                    'actor_id' => $permission->owner_id,
                    'target_user_id' => $permission->requester_id,
                    'activity_type' => $permission->status === 'approved' ? 'permission_granted' : 'permission_rejected',
                    'filename' => $filename,
                    'status' => $permission->status,
                    'occurred_at' => $permission->responded_at,
                    'created_at' => $permission->responded_at,
                    'updated_at' => $permission->responded_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
