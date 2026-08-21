<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evidence_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_upload_id')->constrained('evidence_uploads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('file_path');
            $table->string('original_filename');
            $table->enum('status', ['rejected', 'pending', 'approved', 'deleted'])->default('pending');
            $table->text('rejection_note')->nullable();
            $table->timestamp('uploaded_at');
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('deletion_note')->nullable();
            $table->timestamps();

            $table->index(['evidence_upload_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_revisions');
    }
};
