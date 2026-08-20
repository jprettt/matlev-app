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
        'user_id',
        'file_path',
        'original_filename',
        'status',
        'rejection_note',
        'uploaded_at',
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
}