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
        if (strtoupper((string) $this->evidence_mode) === 'NONE') {
            return 'COMPLETED';
        }

        $requirements = $this->evidenceRequirements;
        $slots = $requirements->flatMap(fn ($requirement) => $requirement->slots);
        if ($slots->isEmpty()) {
            return 'NOT_STARTED';
        }

        $uploads = $slots->map(fn ($slot) => $slot->currentEvidence)->filter();
        if ($uploads->contains('status', 'rejected')) {
            return 'NEEDS_REVISION';
        }
        if ($uploads->contains('status', 'pending')) {
            return 'UNDER_REVIEW';
        }
        $requirementsCompleted = $requirements->every(function ($requirement) {
            $approvedCount = $requirement->slots
                ->map(fn ($slot) => $slot->currentEvidence)
                ->filter(fn ($upload) => $upload?->status === 'approved')
                ->count();
            $minimum = $requirement->evidence_mode === 'REPEATABLE'
                ? (int) $requirement->minimum_slots
                : $requirement->slots->where('is_required', true)->count();

            return $approvedCount >= max(1, $minimum);
        });

        if ($requirementsCompleted) {
            return 'COMPLETED';
        }

        return 'NOT_STARTED';
    }

    public function subkriteria()
    {
        return $this->belongsTo(Subkriteria::class, 'sub_criteria_id');
    }
}