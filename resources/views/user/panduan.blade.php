@extends('layouts.fore')

@section('title', 'Panduan Penggunaan & Framework K3')

@section('content')
<div class="space-y-10" x-data="{ activeFaq: null }">

    <!-- PAGE HEADER (FORE STYLE) -->
    <div class="w-full max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 pt-6 sm:pt-10 pb-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-coffee-500/10 text-coffee-700 text-xs font-bold">
                <span>📖 PETUNJUK TEKNIS APLIKASI</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 font-display">
                Panduan Pengisian Maturity Level K3
            </h1>
            <p class="text-stone-600 text-xs sm:text-sm leading-relaxed">
                Pelajari mekanisme pengunggahan bukti dokumen eviden, standar penilaian, dan klasifikasi tingkat maturitas K3 PLN.
            </p>
        </div>

        <a href="{{ route('user.kriteria') }}" class="px-5 py-2.5 bg-pln-900 hover:bg-pln-800 text-white text-xs font-bold rounded-full transition shadow-sm shrink-0 flex items-center gap-2 self-start md:self-auto">
            <span>Mulai Isi Kriteria</span>
            <span>&rarr;</span>
        </a>
    </div>

    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
    <!-- 1. ALUR PENGISIAN & EVALUASI (5 LANGKAH) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-6">
        <div>
            <h2 class="text-lg sm:text-xl font-extrabold text-stone-900 font-display">Alur Pengisian & Verifikasi</h2>
            <p class="text-xs text-stone-500">Tahapan proses dari pengunggahan dokumen hingga validasi tim penilai</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            
            <div class="bg-stone-100 p-5 rounded-2xl border border-stone-200 space-y-2 relative">
                <span class="w-8 h-8 rounded-full bg-pln-900 text-white font-extrabold text-xs flex items-center justify-center font-display">1</span>
                <h3 class="font-bold text-stone-900 text-sm">Pilih Kriteria</h3>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Buka menu Daftar Kriteria dan pilih subkriteria yang ingin dilengkapi evidennya.
                </p>
            </div>

            <div class="bg-stone-100 p-5 rounded-2xl border border-stone-200 space-y-2 relative">
                <span class="w-8 h-8 rounded-full bg-pln-900 text-white font-extrabold text-xs flex items-center justify-center font-display">2</span>
                <h3 class="font-bold text-stone-900 text-sm">Upload PDF</h3>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Lampirkan file PDF (maks. 10MB) yang memuat bukti pemenuhan indikator level.
                </p>
            </div>

            <div class="bg-cream-100 p-5 rounded-2xl border border-stone-200 space-y-2 relative">
                <span class="w-8 h-8 rounded-full bg-amber-600 text-white font-extrabold text-xs flex items-center justify-center font-display">3</span>
                <h3 class="font-bold text-stone-900 text-sm">Penilaian Admin</h3>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Tim penilai/evaluator K3 memeriksa keabsahan dan relevansi dokumen yang diunggah.
                </p>
            </div>

            <div class="bg-cream-100 p-5 rounded-2xl border border-stone-200 space-y-2 relative">
                <span class="w-8 h-8 rounded-full bg-rose-600 text-white font-extrabold text-xs flex items-center justify-center font-display">4</span>
                <h3 class="font-bold text-stone-900 text-sm">Revisi (Jika Ada)</h3>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Jika ditolak, baca catatan evaluator di menu Perlu Revisi dan unggah file perbaikan.
                </p>
            </div>

            <div class="bg-cream-100 p-5 rounded-2xl border border-stone-200 space-y-2 relative">
                <span class="w-8 h-8 rounded-full bg-emerald-700 text-white font-extrabold text-xs flex items-center justify-center font-display">5</span>
                <h3 class="font-bold text-stone-900 text-sm">Disetujui</h3>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Dokumen valid otomatis menambah persentase skor kematangan unit kerja Anda.
                </p>
            </div>

        </div>
    </div>

    <!-- 2. PENJELASAN 5 TINGKAT LEVEL KEMATANGAN K3 -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-6">
        <div>
            <h2 class="text-lg sm:text-xl font-extrabold text-stone-900 font-display">5 Tingkat Level Kematangan K3</h2>
            <p class="text-xs text-stone-500">Karakteristik implementasi keselamatan kerja pada setiap level</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            
            <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200 space-y-2">
                <span class="px-2.5 py-1 rounded bg-stone-200 text-stone-800 text-xs font-extrabold">Level 1</span>
                <h3 class="font-bold text-stone-900 text-sm">Inisiasi (Initial)</h3>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Proses K3 bersifat ad-hoc, reaktif terhadap insiden, belum terdokumentasi formal secara menyeluruh.
                </p>
            </div>

            <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200 space-y-2">
                <span class="px-2.5 py-1 rounded bg-blue-100 text-blue-900 text-xs font-extrabold">Level 2</span>
                <h3 class="font-bold text-stone-900 text-sm">Berkembang (Developing)</h3>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Prosedur dasar K3 mulai disusun, dipatuhi dalam operasional rutin, dan mulai didokumentasikan.
                </p>
            </div>

            <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200 space-y-2">
                <span class="px-2.5 py-1 rounded bg-amber-100 text-amber-900 text-xs font-extrabold">Level 3</span>
                <h3 class="font-bold text-stone-900 text-sm">Terdefinisi (Defined)</h3>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Standar K3 terintegrasi dalam SOP unit kerja, ada pelatihan berkala, dan pemantauan terstruktur.
                </p>
            </div>

            <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200 space-y-2">
                <span class="px-2.5 py-1 rounded bg-emerald-100 text-emerald-900 text-xs font-extrabold">Level 4</span>
                <h3 class="font-bold text-stone-900 text-sm">Terkelola (Managed)</h3>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Kinerja K3 diukur dengan KPI kuantitatif, analisis risiko prediktif, dan audit internal berkala.
                </p>
            </div>

            <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200 space-y-2">
                <span class="px-2.5 py-1 rounded bg-fore-100 text-fore-900 text-xs font-extrabold">Level 5</span>
                <h3 class="font-bold text-stone-900 text-sm">Optimal (Optimizing)</h3>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Budaya K3 berkelanjutan (*generative safety culture*), inovasi keselamatan kerja, dan perbaikan terus-menerus.
                </p>
            </div>

        </div>
    </div>

    <!-- 3. KETENTUAN BERKAS & FAQ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Ketentuan Dokumen Eviden -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <h2 class="text-lg font-extrabold text-stone-900 font-display">Ketentuan Berkas Eviden</h2>
            <ul class="space-y-3 text-xs sm:text-sm text-stone-600">
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-fore-100 text-fore-900 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</span>
                    <span><strong>Format Berkas:</strong> Wajib bertipe dokumen <strong>PDF (.pdf)</strong>.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-fore-100 text-fore-900 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</span>
                    <span><strong>Ukuran Maksimal:</strong> Ukuran file maksimal adalah <strong>10 Megabytes (MB)</strong> per slot level.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-fore-100 text-fore-900 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</span>
                    <span><strong>Kelengkapan Bukti:</strong> Pastikan dokumen memuat tanggal, tanda tangan pengesahan, atau stempel unit yang sah.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-fore-100 text-fore-900 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✓</span>
                    <span><strong>Sistem Pengisian:</strong> Pengisian bersifat <em>First-Come First-Served</em> untuk slot yang belum terisi.</span>
                </li>
            </ul>
        </div>

        <!-- FAQ Interaktif -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <h2 class="text-lg font-extrabold text-stone-900 font-display">Pertanyaan yang Sering Diajukan (FAQ)</h2>
            
            <div class="space-y-2.5 text-xs sm:text-sm">
                <!-- FAQ 1 -->
                <div class="border border-stone-200 rounded-2xl overflow-hidden">
                    <button @click="activeFaq = (activeFaq === 1 ? null : 1)" 
                            class="w-full text-left p-3.5 bg-cream-100 hover:bg-cream-200 font-bold text-stone-900 flex justify-between items-center transition">
                        <span>Bagaimana jika dokumen saya ditolak?</span>
                        <span x-text="activeFaq === 1 ? '−' : '+'" class="text-base font-bold text-fore-900"></span>
                    </button>
                    <div x-show="activeFaq === 1" x-collapse class="p-3.5 bg-white text-stone-600 leading-relaxed border-t border-stone-200">
                        Buka menu <strong>"Perlu Revisi"</strong> pada navigasi atas. Anda dapat membaca catatan perbaikan yang diberikan evaluator, lalu mengunggah file revisi baru pada form yang tersedia.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="border border-stone-200 rounded-2xl overflow-hidden">
                    <button @click="activeFaq = (activeFaq === 2 ? null : 2)" 
                            class="w-full text-left p-3.5 bg-cream-100 hover:bg-cream-200 font-bold text-stone-900 flex justify-between items-center transition">
                        <span>Berapa lama proses penilaian dokumen?</span>
                        <span x-text="activeFaq === 2 ? '−' : '+'" class="text-base font-bold text-fore-900"></span>
                    </button>
                    <div x-show="activeFaq === 2" x-collapse class="p-3.5 bg-white text-stone-600 leading-relaxed border-t border-stone-200">
                        Evaluasi berkas dilakukan oleh tim asesor/admin K3 secara berkala. Status dokumen dapat dipantau langsung pada menu <strong>"Riwayat Aktivitas"</strong> atau <strong>"Beranda"</strong>.
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="border border-stone-200 rounded-2xl overflow-hidden">
                    <button @click="activeFaq = (activeFaq === 3 ? null : 3)" 
                            class="w-full text-left p-3.5 bg-cream-100 hover:bg-cream-200 font-bold text-stone-900 flex justify-between items-center transition">
                        <span>Apakah boleh mengunggah file selain format PDF?</span>
                        <span x-text="activeFaq === 3 ? '−' : '+'" class="text-base font-bold text-fore-900"></span>
                    </button>
                    <div x-show="activeFaq === 3" x-collapse class="p-3.5 bg-white text-stone-600 leading-relaxed border-t border-stone-200">
                        Tidak. Demi standarisasi dan kemudahan evaluasi, sistem hanya menerima berkas dalam format PDF dengan ukuran maksimal 10MB.
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>

</div>
@endsection
