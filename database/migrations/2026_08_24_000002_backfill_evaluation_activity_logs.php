<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $verifierId = DB::table('users')->where('role', 'admin')->value('id');

        if (! $verifierId) {
            return;
        }

        DB::table('evidence_uploads')
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('id')
            ->eachById(function ($upload) use ($verifierId) {
                $alreadyLogged = DB::table('activity_logs')
                    ->where('evidence_upload_id', $upload->id)
                    ->where('activity_type', 'evaluation')
                    ->where('status', $upload->status)
                    ->exists();

                if ($alreadyLogged) {
                    return;
                }

                DB::table('activity_logs')->insert([
                    'evidence_upload_id' => $upload->id,
                    'maturity_level_id' => $upload->maturity_level_id,
                    'actor_id' => $verifierId,
                    'activity_type' => 'evaluation',
                    'filename' => $upload->original_filename,
                    'status' => $upload->status,
                    'occurred_at' => $upload->updated_at ?? $upload->uploaded_at ?? $upload->created_at,
                    'created_at' => $upload->updated_at ?? $upload->created_at,
                    'updated_at' => $upload->updated_at ?? $upload->created_at,
                ]);
            });
    }

    public function down(): void
    {
        // Backfilled logs are retained as part of the activity history.
    }
};
