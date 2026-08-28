<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_uploads', function (Blueprint $table) {
            $table->dropForeign(['maturity_level_id']);
            $table->dropUnique('evidence_uploads_maturity_level_id_unique');
            $table->foreign('maturity_level_id')->references('id')->on('maturity_levels')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('evidence_uploads', function (Blueprint $table) {
            $table->dropForeign(['maturity_level_id']);
            $table->unique('maturity_level_id');
            $table->foreign('maturity_level_id')->references('id')->on('maturity_levels')->onDelete('cascade');
        });
    }
};