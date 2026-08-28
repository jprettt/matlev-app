<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maturity_levels', function (Blueprint $table) {
            $table->unsignedTinyInteger('level_number')->nullable()->after('level');
            $table->string('evidence_mode')->default('REQUIRED')->after('evidence_requirement');
        });

        DB::statement('UPDATE maturity_levels SET level_number = level WHERE level_number IS NULL');

        DB::table('evidence_requirements')->orderBy('id')->eachById(function ($requirement) {
            DB::table('evidence_slots')->insert([
                'evidence_requirement_id' => $requirement->id,
                'name' => $requirement->name,
                'description' => $requirement->description,
                'is_required' => $requirement->is_required,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('evidence_uploads', function (Blueprint $table) {
            $table->foreignId('evidence_slot_id')->nullable()->after('evidence_requirement_id')->constrained('evidence_slots')->nullOnDelete();
            $table->index(['evidence_slot_id', 'status']);
        });

        DB::table('evidence_uploads')->whereNull('evidence_slot_id')->orderBy('id')->eachById(function ($upload) {
            $slotId = DB::table('evidence_slots')
                ->where('evidence_requirement_id', $upload->evidence_requirement_id)
                ->value('id');

            if ($slotId) {
                DB::table('evidence_uploads')->where('id', $upload->id)->update(['evidence_slot_id' => $slotId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('evidence_uploads', function (Blueprint $table) {
            $table->dropForeign(['evidence_slot_id']);
            $table->dropIndex(['evidence_slot_id', 'status']);
            $table->dropColumn('evidence_slot_id');
        });

        Schema::dropIfExists('evidence_slots');

        Schema::table('maturity_levels', function (Blueprint $table) {
            $table->dropColumn(['level_number', 'evidence_mode']);
        });
    }
};
