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

    public function subkriteria()
    {
        return $this->belongsTo(Subkriteria::class, 'sub_criteria_id');
    }
}