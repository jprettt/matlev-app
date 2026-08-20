<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Kriteria (K3 Program Kerja)
        Schema::create('criterias', function (Blueprint $table) {
            $table->id();
            $table->string('code'); // e.g. "1", "2"
            $table->string('title'); // e.g. "Leadership & Management Commitment"
            $table->timestamps();
        });

        // 2. Tabel Subkriteria
        Schema::create('sub_criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criteria_id')->constrained('criterias')->onDelete('cascade');
            $table->string('code'); // e.g. "1.1", "1.2"
            $table->string('title'); // e.g. "Menyusun RKAP Bidang K3"
            $table->text('description')->nullable();
            $table->string('pic')->nullable(); // e.g. "Perencanaan dan K3"
            $table->timestamps();
        });

        // 3. Tabel Maturity Levels (Deskripsi Level 1 - 5)
        Schema::create('maturity_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_criteria_id')->constrained('sub_criterias')->onDelete('cascade');
            $table->unsignedTinyInteger('level'); // 1, 2, 3, 4, 5
            $table->text('description')->nullable();
            $table->text('evidence_requirement')->nullable(); // Bukti yang dibutuhkan
            $table->timestamps();
            
            $table->unique(['sub_criteria_id', 'level']);
        });

        // 4. Tabel Upload Bukti (Unique Constraint pada maturity_level_id)
        Schema::create('evidence_uploads', function (Blueprint $table) {
            $table->id();
            // UNIQUE menjamin slot indikator ini TIDAK BISA di-overwrite oleh user lain!
            $table->foreignId('maturity_level_id')->unique()->constrained('maturity_levels')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_filename');
            $table->timestamp('uploaded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_uploads');
        Schema::dropIfExists('maturity_levels');
        Schema::dropIfExists('sub_criterias');
        Schema::dropIfExists('criterias');
    }
};