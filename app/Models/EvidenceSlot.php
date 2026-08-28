<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenceSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'evidence_requirement_id',
        'name',
        'description',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function evidenceRequirement()
    {
        return $this->belongsTo(EvidenceRequirement::class);
    }

    public function evidenceUploads()
    {
        return $this->hasMany(EvidenceUpload::class);
    }

    public function currentEvidence()
    {
        return $this->hasOne(EvidenceUpload::class)->latestOfMany();
    }

    public function isRepeatable(): bool
    {
        return $this->evidenceRequirement?->evidence_mode === 'REPEATABLE';
    }
}
