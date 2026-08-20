<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenceUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'maturity_level_id',
        'user_id',
        'file_path',
        'original_filename',
        'status',          // Tambahkan ini
        'rejection_note',  // Tambahkan ini
        'uploaded_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function maturityLevel()
    {
        return $this->belongsTo(MaturityLevel::class);
    }
}