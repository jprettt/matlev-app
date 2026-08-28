<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenceRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'maturity_level_id',
        'name',
        'description',
        'is_required',
        'allowed_file_type',
        'max_file_size',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'max_file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function maturityLevel()
    {
        return $this->belongsTo(MaturityLevel::class);
    }

    public function evidenceUploads()
    {
        return $this->hasMany(EvidenceUpload::class);
    }

    public function currentEvidence()
    {
        return $this->hasOne(EvidenceUpload::class)->latestOfMany();
    }
}
