<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('evidence_revisions', function (Blueprint $table) {
            $table->boolean('is_current')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('evidence_revisions', function (Blueprint $table) {
            $table->dropColumn('is_current');
        });
    }
};
