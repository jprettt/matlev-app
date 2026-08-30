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
        $scores = $this->subKriterias
            ->map(fn ($sub) => (float) $sub->scoreForUser($userId))
            ->filter(fn ($score) => $score !== null)
            ->values();

        if ($scores->isEmpty()) {
            return 0.0;
        }

        return round($scores->avg(), 2);
    }
}