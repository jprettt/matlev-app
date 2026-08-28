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
        'user_id',
        'file_path',
        'original_filename',
        'file_size',
        'mime_type',
        'status',
        'rejection_note',
        'uploaded_at',
        'reviewed_at',
        'reviewed_by',
    ];

    /**
     * Ensure uploaded_at is cast to a DateTime (Carbon) instance.
     */
    protected $casts = [
        'uploaded_at' => 'datetime',
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