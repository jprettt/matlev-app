<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_uploads', function (Blueprint $table) {
            $table->foreignId('evidence_requirement_id')->nullable()->after('maturity_level_id')->constrained('evidence_requirements')->nullOnDelete();
            $table->unsignedBigInteger('file_size')->nullable()->after('original_filename');
            $table->string('mime_type')->nullable()->after('file_size');
            $table->timestamp('reviewed_at')->nullable()->after('uploaded_at');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->index(['evidence_requirement_id', 'status']);
        });

        Schema::table('document_permission_requests', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('requester_id');
        });
    }

    public function down(): void
    {
        Schema::table('document_permission_requests', function (Blueprint $table) {
            $table->dropColumn('reason');
        });

        Schema::table('evidence_uploads', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['evidence_requirement_id']);
            $table->dropIndex(['evidence_requirement_id', 'status']);
            $table->dropColumn(['evidence_requirement_id', 'file_size', 'mime_type', 'reviewed_at', 'reviewed_by']);
        });
    }
};
