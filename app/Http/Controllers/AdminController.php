<?php

namespace App\Http\Controllers;

use App\Models\Subkriteria;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Mengambil semua data subkriteria beserta relasi kriteria dan user
        $subkriterias = Subkriteria::with(['kriteria', 'user'])->get();

        return view('admin.dashboard', compact('subkriterias'));
    }

    public function evaluate(Request $request, $id)
    {
        $request->validate([
            'skor' => 'required|numeric|min:0|max:100',
            'catatan' => 'nullable|string',
            'status' => 'required|in:Pending,Disetujui,Ditolak',
        ]);

        $subkriteria = Subkriteria::findOrFail($id);
        $subkriteria->update([
            'skor' => $request->skor,
            'catatan' => $request->catatan,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Evaluasi berhasil disimpan.');
    }
}