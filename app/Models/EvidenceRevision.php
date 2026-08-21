<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenceRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'evidence_upload_id',
        'user_id',
        'version_number',
        'file_path',
        'original_filename',
        'status',
        'is_current',
        'rejection_note',
        'uploaded_at',
        'deleted_at',
        'deleted_by',
        'deletion_note',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function evidenceUpload()
    {
        return $this->belongsTo(EvidenceUpload::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
