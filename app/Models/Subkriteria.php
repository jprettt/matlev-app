<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subkriteria extends Model
{
    use HasFactory;

    protected $table = 'sub_criterias';

    protected $guarded = [];

    public function maturityLevels()
    {
        return $this->hasMany(MaturityLevel::class, 'sub_criteria_id');
    }

    public function kriteria()
    {
        return $this->belongsTo(Kriteria::class, 'criteria_id');
    }
}