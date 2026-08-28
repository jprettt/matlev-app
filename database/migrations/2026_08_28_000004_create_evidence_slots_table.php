<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_requirement_id')->constrained('evidence_requirements')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['evidence_requirement_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_slots');
    }
};
