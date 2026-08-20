<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subkriteria extends Model
{
    use HasFactory;

    // Paksa Laravel menggunakan tabel 'sub_criterias' (bahasa Inggris)
    protected $table = 'sub_criterias';
    
    // Mengizinkan mass assignment untuk semua kolom
    protected $guarded = [];

    // Relasi ke MaturityLevel (Satu Subkriteria punya Banyak Level)
    public function maturityLevels()
    {
        // Parameter kedua adalah foreign_key di tabel maturity_levels
        return $this->hasMany(MaturityLevel::class, 'sub_criteria_id'); 
    }

    // Relasi balik ke Kriteria (Banyak Subkriteria milik Satu Kriteria)
    public function kriteria()
    {
        // Parameter kedua adalah foreign_key di tabel sub_criterias
        return $this->belongsTo(Kriteria::class, 'criteria_id'); 
    }
}