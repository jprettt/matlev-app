<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('maturity_levels')->orderBy('id')->eachById(function ($level) {
            $requirementId = DB::table('evidence_requirements')->insertGetId([
                'maturity_level_id' => $level->id,
                'name' => $level->evidence_requirement ?: 'Dokumen pendukung Level ' . $level->level,
                'description' => $level->description,
                'is_required' => true,
                'allowed_file_type' => 'pdf',
                'max_file_size' => 10240,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('evidence_uploads')
                ->where('maturity_level_id', $level->id)
                ->whereNull('evidence_requirement_id')
                ->update(['evidence_requirement_id' => $requirementId]);
        });
    }

    public function down(): void
    {
        DB::table('evidence_uploads')->update(['evidence_requirement_id' => null]);
        DB::table('evidence_requirements')->delete();
    }
};
