<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kriteria extends Model
{
    use HasFactory;

    // Paksa Laravel menggunakan tabel 'criterias' (bahasa Inggris)
    protected $table = 'criterias'; 
    
    // Mengizinkan mass assignment untuk semua kolom
    protected $guarded = [];

    // Relasi ke Subkriteria (Satu Kriteria punya Banyak Subkriteria)
    public function subKriterias()
    {
        // Parameter kedua adalah foreign_key di tabel sub_criterias
        return $this->hasMany(Subkriteria::class, 'criteria_id'); 
    }
}