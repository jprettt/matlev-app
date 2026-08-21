<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('document_permission_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_upload_id')->constrained('evidence_uploads')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['edit', 'delete']);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            // INI YANG DIUBAH: Ditambahin 'doc_perm_req_idx' di belakangnya
            $table->index(['evidence_upload_id', 'requester_id', 'action', 'status'], 'doc_perm_req_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_permission_requests');
    }
};