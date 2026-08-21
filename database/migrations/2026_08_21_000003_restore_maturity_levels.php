<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('maturity_levels')) {
            return;
        }

        DB::table('sub_criterias')->orderBy('id')->each(function ($subcriteria): void {
            foreach (range(1, 5) as $level) {
                $exists = DB::table('maturity_levels')
                    ->where('sub_criteria_id', $subcriteria->id)
                    ->where('level', $level)
                    ->exists();

                if (! $exists) {
                    DB::table('maturity_levels')->insert([
                        'sub_criteria_id' => $subcriteria->id,
                        'level' => $level,
                        'description' => "Deskripsi Indikator Level {$level}",
                        'evidence_requirement' => "Bukti PDF Dokumen Level {$level}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        DB::table('maturity_levels')
            ->whereBetween('level', [3, 5])
            ->delete();
    }
};
