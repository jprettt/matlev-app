<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenceUpload extends Model
{
    use HasFactory;

    protected $table = 'evidence_uploads';

    protected $fillable = [
        'maturity_level_id',
        'evidence_requirement_id',
        'evidence_slot_id',
        'user_id',
        'file_path',
        'original_filename',
        'file_size',
        'mime_type',
        'version',
        'status',
        'rejection_note',
        'rejection_reason',
        'uploaded_at',
        'reviewed_at',
        'reviewed_by',
        'submitted_at',
        'is_current',
    ];

    /**
     * Ensure uploaded_at is cast to a DateTime (Carbon) instance.
     */
    protected $casts = [
        'uploaded_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'is_current' => 'boolean',
        'version' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function maturityLevel()
    {
        return $this->belongsTo(MaturityLevel::class, 'maturity_level_id');
    }

    public function evidenceRequirement()
    {
        return $this->belongsTo(EvidenceRequirement::class);
    }

    public function evidenceSlot()
    {
        return $this->belongsTo(EvidenceSlot::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function permissionRequests()
    {
        return $this->hasMany(DocumentPermissionRequest::class);
    }

    public function revisions()
    {
        return $this->hasMany(EvidenceRevision::class)->orderByDesc('version_number');
    }
}