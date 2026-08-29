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

    public function scoreForUser(?int $userId = null): float
    {
        if ($this->subKriterias->isEmpty()) {
            return 0;
        }

        return round($this->subKriterias->avg(fn ($sub) => $sub->scoreForUser($userId)), 2);
    }
}