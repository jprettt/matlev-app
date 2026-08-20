<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Update tabel users bawaan
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'user'])->default('user')->after('email');
            }
            if (!Schema::hasColumn('users', 'unit_kerja')) {
                $table->string('unit_kerja')->nullable()->after('role');
            }
        });

        // Tabel Kriteria / Elemen K3
        Schema::create('kriterias', function (Blueprint $table) {
            $table->id();
            $table->string('kode_elemen'); // Contoh: "1.1", "1.2"
            $table->string('nama_elemen');
            $table->timestamps();
        });

        // Tabel Subkriteria / Indikator & Pengajuan Dokumen
        Schema::create('subkriterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kriteria_id')->constrained('kriterias')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->text('deskripsi_indikator');
            $table->string('file_path')->nullable();
            $table->string('file_original_name')->nullable();
            $table->enum('status', ['Belum Upload', 'Pending', 'Disetujui', 'Revisi'])->default('Belum Upload');
            $table->integer('skor_level')->nullable(); // 1 - 5
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subkriterias');
        Schema::dropIfExists('kriterias');
    }
};