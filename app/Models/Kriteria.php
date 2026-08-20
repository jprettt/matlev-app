<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kriteria extends Model
{
    use HasFactory;

    protected $table = 'criterias';

    protected $guarded = [];

    public function subKriterias()
    {
        return $this->hasMany(Subkriteria::class, 'criteria_id');
    }
}