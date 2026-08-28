<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'evidence_upload_id',
        'maturity_level_id',
        'actor_id',
        'target_user_id',
        'activity_type',
        'filename',
        'status_before',
        'status',
        'note',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function evidenceUpload()
    {
        return $this->belongsTo(EvidenceUpload::class);
    }

    public function maturityLevel()
    {
        return $this->belongsTo(MaturityLevel::class);
    }
}
