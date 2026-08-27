<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_uploads', function (Blueprint $table) {
            $table->dropUnique('evidence_uploads_maturity_level_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('evidence_uploads', function (Blueprint $table) {
            $table->unique('maturity_level_id');
        });
    }
};