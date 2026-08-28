<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maturity_level_id')->constrained('maturity_levels')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('allowed_file_type')->default('pdf');
            $table->unsignedInteger('max_file_size')->default(10240);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['maturity_level_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_requirements');
    }
};
