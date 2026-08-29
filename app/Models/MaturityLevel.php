<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaturityLevel extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function evidenceUpload()
    {
        return $this->hasOne(EvidenceUpload::class, 'maturity_level_id');
    }

    public function evidenceUploads()
    {
        return $this->hasMany(EvidenceUpload::class, 'maturity_level_id');
    }

    public function evidenceRequirements()
    {
        return $this->hasMany(EvidenceRequirement::class, 'maturity_level_id')->orderBy('sort_order');
    }

    public function getComputedStatusAttribute(): string
    {
        return $this->statusForUser(null);
    }

    public function statusForUser(?int $userId = null): string
    {
        if (strtoupper((string) $this->evidence_mode) === 'NONE') {
            return 'COMPLETED';
        }

        $requirements = $this->evidenceRequirements;
        $slots = $requirements->flatMap(fn ($requirement) => $requirement->slots);
        if ($slots->isEmpty()) {
            return 'NOT_STARTED';
        }

        $uploads = $slots->map(fn ($slot) => $this->currentUploadForUser($slot, $userId))->filter();
        if ($uploads->contains('status', 'rejected')) {
            return 'NEEDS_REVISION';
        }
        if ($uploads->contains('status', 'pending')) {
            return 'UNDER_REVIEW';
        }
        $requirementsCompleted = $requirements->every(function ($requirement) use ($userId) {
            return $requirement->slots->every(fn ($slot) =>
                $this->currentUploadForUser($slot, $userId)?->status === 'approved'
            );
        });

        if ($requirementsCompleted) {
            return 'COMPLETED';
        }

        return 'NOT_STARTED';
    }

    public function scoreForUser(?int $userId): int
    {
        if (strtoupper((string) $this->evidence_mode) === 'NONE') {
            return (int) ($this->level_number ?: $this->level);
        }

        return $this->statusForUser($userId) === 'COMPLETED'
            ? (int) ($this->level_number ?: $this->level)
            : 0;
    }

    public function hasAllRequiredFiles(): bool
    {
        if (strtoupper((string) $this->evidence_mode) === 'NONE') {
            return true;
        }

        $requirements = $this->evidenceRequirements;
        $slots = $requirements->flatMap(fn ($requirement) => $requirement->slots);
        if ($slots->isEmpty()) {
            return $this->evidenceUploads()->exists();
        }

        return $requirements->every(fn ($requirement) =>
            $requirement->slots->every(fn ($slot) => $slot->evidenceUploads()->exists())
        );
    }

    public function currentUploadForUser(EvidenceSlot $slot, ?int $userId): ?EvidenceUpload
    {
        $uploads = $slot->relationLoaded('evidenceUploads')
            ? $slot->evidenceUploads
            : $slot->evidenceUploads()->get();

        if ($userId !== null) {
            $uploads = $uploads->where('user_id', $userId);
        }

        return $uploads->sortByDesc('id')->first();
    }

    public function subkriteria()
    {
        return $this->belongsTo(Subkriteria::class, 'sub_criteria_id');
    }
}