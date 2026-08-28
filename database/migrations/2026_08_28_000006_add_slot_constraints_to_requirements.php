<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_requirements', function (Blueprint $table) {
            $table->unsignedInteger('minimum_slots')->default(1)->after('is_required');
            $table->unsignedInteger('maximum_slots')->nullable()->after('minimum_slots');
            $table->string('allowed_file_types')->nullable()->after('allowed_file_type');
            $table->string('evidence_mode')->default('FIXED')->after('maximum_slots');
        });
    }

    public function down(): void
    {
        Schema::table('evidence_requirements', function (Blueprint $table) {
            $table->dropColumn(['minimum_slots', 'maximum_slots', 'allowed_file_types', 'evidence_mode']);
        });
    }
};
