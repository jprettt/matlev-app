<?php

namespace App\Http\Controllers;

use App\Models\Kriteria;
use App\Models\Subkriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        // Mengambil semua Kriteria beserta Subkriteria
        $kriterias = Kriteria::with('subkriterias')->get();

        return view('user.upload', compact('kriterias'));
    }

    public function upload(Request $request, $id)
    {
        $request->validate([
            'document' => 'required|mimes:pdf|max:10240',
        ]);

        $subkriteria = Subkriteria::findOrFail($id);

        if ($subkriteria->file_path && Storage::disk('public')->exists($subkriteria->file_path)) {
            Storage::disk('public')->delete($subkriteria->file_path);
        }

        $file = $request->file('document');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('documents', $filename, 'public');

        $subkriteria->update([
            'user_id' => Auth::id(),
            'file_path' => $path,
            'file_original_name' => $file->getClientOriginalName(),
            'status' => 'Pending',
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah dan menunggu evaluasi.');
    }
}