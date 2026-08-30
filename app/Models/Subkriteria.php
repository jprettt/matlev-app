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

    public function scoreForUser(?int $userId = null): int
    {
        $scores = $this->maturityLevels
            ->map(fn ($level) => (float) $level->scoreForUser($userId))
            ->filter(fn ($score) => $score !== null)
            ->values();

        if ($scores->isEmpty()) {
            return 0;
        }

        return (int) $scores->max();
    }
}