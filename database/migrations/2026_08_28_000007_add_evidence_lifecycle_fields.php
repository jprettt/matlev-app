<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('maturity_levels', 'title')) {
            Schema::table('maturity_levels', fn (Blueprint $table) => $table->string('title')->nullable()->after('level_number'));
        }
        if (! Schema::hasColumn('maturity_levels', 'overall_description')) {
            Schema::table('maturity_levels', fn (Blueprint $table) => $table->text('overall_description')->nullable()->after('description'));
        }
        if (! Schema::hasColumn('evidence_uploads', 'version')) {
            Schema::table('evidence_uploads', fn (Blueprint $table) => $table->unsignedInteger('version')->default(1)->after('mime_type'));
        }
        if (! Schema::hasColumn('evidence_uploads', 'rejection_reason')) {
            Schema::table('evidence_uploads', fn (Blueprint $table) => $table->text('rejection_reason')->nullable()->after('rejection_note'));
        }
        if (! Schema::hasColumn('evidence_uploads', 'submitted_at')) {
            Schema::table('evidence_uploads', fn (Blueprint $table) => $table->timestamp('submitted_at')->nullable()->after('uploaded_at'));
        }
        if (! Schema::hasColumn('evidence_uploads', 'is_current')) {
            Schema::table('evidence_uploads', fn (Blueprint $table) => $table->boolean('is_current')->default(true)->after('reviewed_by'));
        }

        DB::statement('UPDATE maturity_levels SET overall_description = description WHERE overall_description IS NULL');
        DB::statement("UPDATE evidence_uploads SET rejection_reason = rejection_note WHERE rejection_reason IS NULL");
        DB::statement('UPDATE evidence_uploads SET submitted_at = uploaded_at WHERE submitted_at IS NULL');
    }

    public function down(): void
    {
        Schema::table('evidence_uploads', function (Blueprint $table) {
            $table->dropColumn(['version', 'rejection_reason', 'submitted_at', 'is_current']);
        });

        Schema::table('maturity_levels', function (Blueprint $table) {
            $table->dropColumn(['title', 'overall_description']);
        });
    }
};