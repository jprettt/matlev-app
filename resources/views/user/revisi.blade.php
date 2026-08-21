@extends('layouts.fore')

@section('title', 'Dokumen Perlu Revisi')

@section('content')
<div class="space-y-8">

    <!-- PAGE HEADER (FORE STYLE) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-800 text-xs font-bold">
                <span>❌ TINDAK LANJUT EVALUASI</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 font-display">
                Dokumen yang Memerlukan Revisi
            </h1>
            <p class="text-stone-600 text-xs sm:text-sm leading-relaxed">
                Berikut adalah daftar berkas yang ditolak oleh tim penilai K3. Perbaiki file sesuai catatan lalu unggah ulang file PDF pengganti.
            </p>
        </div>

        @if(count($rejectedItems) > 0)
            <div class="bg-rose-50 border border-rose-200 px-4 py-2.5 rounded-2xl flex items-center gap-3 shrink-0">
                <span class="w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center font-extrabold text-sm">
                    {{ count($rejectedItems) }}
                </span>
                <span class="text-xs font-bold text-rose-900">Dokumen Harus Diperbaiki</span>
            </div>
        @endif
    </div>

    <!-- LIST REVISION ITEMS -->
    @if(count($rejectedItems) > 0)
        <div class="space-y-4">
            @foreach($rejectedItems as $item)
                <div class="bg-white p-6 rounded-3xl border-2 border-rose-200 shadow-sm flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 hover:shadow-md transition-shadow">
                    
                    <!-- Left: Details & Rejection Note -->
                    <div class="space-y-3 flex-grow">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="bg-rose-600 text-white text-xs font-extrabold px-3 py-1 rounded-full">
                                Level {{ $item['level'] }}
                            </span>
                            <span class="bg-stone-100 text-stone-700 text-xs font-bold px-2.5 py-1 rounded-full">
                                {{ $item['criteria_code'] }} - {{ $item['sub_code'] }}
                            </span>
                            <h2 class="font-bold text-stone-900 text-base">
                                {{ $item['criteria'] }} &rsaquo; {{ $item['sub'] }}
                            </h2>
                        </div>

                        <div class="space-y-1">
                            <p class="text-xs text-stone-500 font-semibold uppercase tracking-wider">Persyaratan Eviden:</p>
                            <p class="text-xs text-stone-700 bg-stone-50 p-3 rounded-xl border border-stone-200 leading-relaxed">
                                {{ $item['requirement'] }}
                            </p>
                        </div>

                        <!-- Catatan Evaluator / Admin -->
                        <div class="bg-rose-50 border border-rose-200 p-3.5 rounded-2xl space-y-1">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-rose-900">
                                <span>⚠️</span>
                                <span>Catatan Penolakan dari Tim Penilai:</span>
                            </div>
                            <p class="text-xs text-rose-800 font-medium leading-relaxed pl-5">
                                {{ $item['upload']->rejection_note ?? 'Tidak ada catatan khusus, silakan sesuaikan dengan indikator level.' }}
                            </p>
                        </div>

                        <div class="text-[11px] text-stone-400 flex items-center gap-2">
                            <span>File sebelumnya: <strong class="text-stone-600">{{ $item['upload']->original_filename }}</strong></span>
                            <span>•</span>
                            <a href="{{ asset('storage/' . $item['upload']->file_path) }}" target="_blank" class="text-fore-900 hover:underline font-bold">
                                Unduh File Lama
                            </a>
                        </div>
                    </div>

                    <!-- Right: Form Re-upload Revisi (Fungsionalitas Asli) -->
                    <div class="w-full lg:w-80 bg-cream-100 p-4 sm:p-5 rounded-2xl border border-stone-200 shrink-0 space-y-3">
                        <div class="flex items-center gap-2 text-xs font-bold text-stone-800">
                            <span>🔄</span>
                            <span>Unggah Berkas Pengganti:</span>
                        </div>

                        <form x-data="{ selectedFile: false }" action="{{ route('matlev.upload', $item['upload']->maturity_level_id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <input type="file" name="pdf_file" accept="application/pdf" required @change="selectedFile = true"
                                   class="block w-full text-xs text-stone-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-white file:text-stone-800 file:border file:border-stone-300 hover:file:bg-stone-50 cursor-pointer">
                            <button type="submit" 
                                    :disabled="!selectedFile"
                                    :class="selectedFile ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-rose-300 text-rose-100 cursor-not-allowed'"
                                    class="w-full text-xs font-bold py-2.5 px-4 rounded-full transition shadow-sm flex items-center justify-center gap-2">
                                <span>Kirim File Revisi</span>
                                <span>&rarr;</span>
                            </button>
                        </form>
                        <p class="text-[10px] text-stone-400 text-center">Format dokumen: PDF maks. 10MB</p>
                    </div>

                </div>
            @endforeach
        </div>
    @else
        <!-- EMPTY STATE (NO REVISION NEEDED) -->
        <div class="bg-white p-12 sm:p-16 text-center rounded-3xl border border-stone-200 shadow-sm space-y-4 max-w-xl mx-auto">
            <div class="w-20 h-20 bg-emerald-100 text-emerald-700 rounded-full mx-auto flex items-center justify-center text-3xl shadow-inner">
                ✓
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 font-display">Semua Dokumen Baik!</h2>
            <p class="text-xs sm:text-sm text-stone-500 max-w-md mx-auto leading-relaxed">
                Tidak ada berkas yang memerlukan revisi saat ini. Seluruh eviden yang Anda kirimkan telah memenuhi syarat atau sedang dalam proses peninjauan.
            </p>
            <div class="pt-2">
                <a href="{{ route('user.kriteria') }}" class="px-6 py-2.5 bg-fore-900 hover:bg-fore-800 text-white text-xs font-bold rounded-full transition shadow-sm inline-block">
                    Lihat Daftar Kriteria &rarr;
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
