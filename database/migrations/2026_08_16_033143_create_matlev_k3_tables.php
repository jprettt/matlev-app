<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('criterias')) {
            Schema::create('criterias', function (Blueprint $table) {
                $table->id();
                $table->string('code');
                $table->string('title');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sub_criterias')) {
            Schema::create('sub_criterias', function (Blueprint $table) {
                $table->id();
                $table->foreignId('criteria_id')->constrained('criterias')->onDelete('cascade');
                $table->string('code');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('pic')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('maturity_levels')) {
            Schema::create('maturity_levels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sub_criteria_id')->constrained('sub_criterias')->onDelete('cascade');
                $table->unsignedTinyInteger('level');
                $table->text('description')->nullable();
                $table->text('evidence_requirement')->nullable();
                $table->timestamps();
                $table->unique(['sub_criteria_id', 'level']);
            });
        }

        if (! Schema::hasTable('evidence_uploads')) {
            Schema::create('evidence_uploads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('maturity_level_id')->unique()->constrained('maturity_levels')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('file_path');
                $table->string('original_filename');
                $table->string('status')->default('pending');
                $table->text('rejection_note')->nullable();
                $table->timestamp('uploaded_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_uploads');
        Schema::dropIfExists('maturity_levels');
        Schema::dropIfExists('sub_criterias');
        Schema::dropIfExists('criterias');
    }
};