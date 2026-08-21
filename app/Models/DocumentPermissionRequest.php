<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentPermissionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'evidence_upload_id',
        'owner_id',
        'requester_id',
        'action',
        'status',
        'responded_at',
        'used_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function evidenceUpload()
    {
        return $this->belongsTo(EvidenceUpload::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
