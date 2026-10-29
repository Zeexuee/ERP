@extends('layouts.app', ['title' => 'Sistem Scan Barcode'])

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Sistem Scan Barcode Gudang</h2>
        </div>

        <div class="flex items-center gap-2">
            <!-- Audio Feedback Toggle -->
            <button 
                type="button" 
                id="toggleSoundBtn" 
                onclick="toggleSound()" 
                class="px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-100 transition shadow-sm"
                title="Aktifkan/Nonaktifkan Suara Beep"
            >
                Suara: <span id="soundStatusText" class="font-bold text-slate-900">Aktif</span>
            </button>

            <!-- Link ke Data Gudang -->
            <a href="{{ route('stin.items.index') }}" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-sm transition">
                Data Gudang
            </a>
        </div>
    </div>

    <!-- Scanner Control Deck (apple-glass-panel) -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md border border-slate-900/10 space-y-4">
        
        <!-- Mode Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-900/10 pb-4">
            <div>
                <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Mode Pemindaian
                </div>
            </div>
            <div class="inline-flex rounded-xl border border-slate-300 bg-white p-1 max-w-xl w-full sm:w-auto shadow-2xs">
                <button 
                    type="button" 
                    onclick="setScanMode('out')" 
                    id="btn-mode-out" 
                    class="flex-1 sm:flex-initial py-1.5 px-3.5 rounded-lg text-xs font-bold transition bg-slate-900 text-white text-center shadow-xs"
                    title="Pengeluaran stok barang gudang"
                >
                    Barang Keluar (-)
                </button>
                <button 
                    type="button" 
                    onclick="setScanMode('in')" 
                    id="btn-mode-in" 
                    class="flex-1 sm:flex-initial py-1.5 px-3.5 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center"
                    title="Penerimaan stok barang gudang"
                >
                    Barang Masuk (+)
                </button>
                <button 
                    type="button" 
                    onclick="setScanMode('loan')" 
                    id="btn-mode-loan" 
                    class="flex-1 sm:flex-initial py-1.5 px-3.5 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center"
                    title="Pencatatan peminjaman barang gudang"
                >
                    Pinjam Barang (-)
                </button>
                <button 
                    type="button" 
                    onclick="setScanMode('lookup')" 
                    id="btn-mode-lookup" 
                    class="flex-1 sm:flex-initial py-1.5 px-3.5 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center"
                    title="Pengecekan info & sisa stok barang"
                >
                    Cek Stok (Lookup)
                </button>
            </div>
        </div>

        <!-- Scan Input Row -->
        <div>
            <form id="scannerForm" onsubmit="handleManualSubmit(event)" class="space-y-3">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative flex-1">
                        <input 
                            type="text" 
                            id="barcodeInput" 
                            name="code" 
                            autocomplete="off" 
                            autofocus 
                            placeholder="Silahkan barcode produk" 
                            class="apple-input w-full rounded-2xl pl-4 pr-28 py-3 text-sm font-mono font-medium border-2 border-slate-900 bg-white text-slate-900 placeholder:text-slate-400 focus:border-slate-900 shadow-xs"
                        >
                        <div id="scannerLivePulse" 
                             style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); -webkit-transform: translateY(-50%); pointer-events: none; z-index: 10;" 
                             class="text-[10px] font-mono text-slate-700 bg-slate-100 border border-slate-300 px-2.5 py-1 rounded-full font-bold select-none">
                            STANDBY
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        id="submitScanBtn" 
                        class="px-6 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition whitespace-nowrap"
                    >
                        + Tambah ke Daftar
                    </button>
                </div>
            </form>
        </div>

        <!-- Notification Message Box -->
        <div id="scanFeedbackBox" class="hidden rounded-xl p-3 text-xs font-semibold transition"></div>

    </div>

    <!-- Main Workspace Grid: Batch Cart & Sidebar Log -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Columns: Batch Cart / Daftar Antrean Mutasi Barang -->
        <div class="lg:col-span-2 space-y-5">
            
            <!-- Batch Cart Section (Active in 'out' & 'in' mode) -->
            <div id="batchCartSection" class="apple-glass-card rounded-3xl p-6 shadow-sm border border-slate-900/10 space-y-5">
                
                <!-- Cart Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-900/10 pb-4">
                    <div class="flex items-center gap-2">
                        <h3 id="batchCartTitle" class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                            Daftar Antrean Barang Keluar
                        </h3>
                        <span id="batchModeBadge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                            Barang Keluar (-)
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span id="batchCountBadge" class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200">
                            0 Jenis Produk
                        </span>
                        <span id="batchTotalQtyBadge" class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200">
                            0 Total Qty
                        </span>
                        <button 
                            type="button" 
                            onclick="clearBatchList()" 
                            id="btnClearBatch" 
                            class="px-2.5 py-1 rounded-xl hover:bg-slate-100 text-slate-600 border border-transparent hover:border-slate-300 text-xs font-semibold transition"
                            title="Kosongkan Antrean"
                        >
                            Reset Daftar
                        </button>
                    </div>
                </div>

                <!-- Empty Cart Placeholder (Iconless & Textless) -->
                <div id="emptyCartPlaceholder" class="py-12 text-center text-slate-400">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Daftar Antrean Kosong</div>
                </div>

                <!-- Cart Table (Populated dynamically) -->
                <div id="cartTableContainer" class="hidden overflow-x-auto border border-slate-900/10 rounded-2xl bg-white shadow-2xs">
                    <table class="w-full text-left text-xs text-slate-800">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-900/10">
                            <tr>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3 text-center">Stok Gudang</th>
                                <th class="px-4 py-3 text-center w-28">Jumlah</th>
                                <th class="px-4 py-3 text-center">Satuan</th>
                                <th class="px-4 py-3 text-center w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cartTableBody" class="divide-y divide-slate-900/10">
                            <!-- Diisi via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Cart Summary & Trigger Action Bar -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-900/10">
                    <div class="text-xs font-semibold text-slate-600">
                        <span id="batchSummaryText">0 Jenis Produk (0 Total Qty)</span>
                    </div>

                    <button 
                        type="button" 
                        id="btnOpenConfirmModal" 
                        onclick="openConfirmModal()" 
                        class="w-full sm:w-auto py-3 px-6 rounded-2xl bg-slate-900 hover:bg-slate-800 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold text-xs sm:text-sm shadow-md transition text-center cursor-pointer"
                        disabled
                    >
                        <span id="btnOpenConfirmModalText">Konfirmasi Mutasi</span>
                    </button>
                </div>

            </div>

            <!-- Lookup Result Section (Visible only when in 'lookup' mode) -->
            <div id="lookupResultSection" class="hidden apple-glass-card rounded-3xl p-6 min-h-[300px] flex flex-col justify-between space-y-5">
                <div id="emptyLookupPlaceholder" class="my-auto py-12 text-center text-slate-400">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Belum Ada Barang Dipindai</div>
                </div>

                <div id="activeLookupDetails" class="hidden space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 border-b border-slate-900/10 pb-4">
                        <div>
                            <span id="detailCategory" class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1"></span>
                            <h3 id="detailName" class="text-lg font-bold text-slate-900"></h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-slate-500 font-medium">SKU:</span>
                                <span id="detailSku" class="font-mono text-xs font-bold text-slate-900"></span>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-300 rounded-2xl px-5 py-3 text-center sm:text-right shadow-xs">
                            <span class="block text-[10px] uppercase font-bold text-slate-500 tracking-wider">Stok Saat Ini</span>
                            <div class="text-2xl font-bold text-slate-900 font-mono mt-0.5">
                                <span id="detailStock">0</span>
                                <span id="detailUnit" class="text-xs font-normal text-slate-600"></span>
                            </div>
                            <div id="detailPrice" class="text-[11px] font-semibold text-slate-700 mt-0.5"></div>
                            <div id="detailLoanedBadge" class="hidden mt-1 text-[10px] font-bold text-slate-700 bg-slate-100 border border-slate-300 px-2 py-0.5 rounded-md inline-block">
                                Sedang Dipinjam: <span id="detailLoanedQty">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Actions & Add to Cart button -->
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <button 
                            type="button" 
                            onclick="addLookupItemToCart('out')" 
                            class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition"
                        >
                            + Masukkan ke Antrean Barang Keluar
                        </button>
                        <button 
                            type="button" 
                            onclick="addLookupItemToCart('loan')" 
                            class="px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-900 text-xs font-bold shadow-2xs transition"
                        >
                            + Masukkan ke Antrean Pinjam
                        </button>
                        <button 
                            type="button" 
                            onclick="addLookupItemToCart('in')" 
                            class="px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-900 text-xs font-bold shadow-sm transition"
                        >
                            + Masukkan ke Antrean Barang Masuk
                        </button>
                        <a 
                            id="detailPrintLink" 
                            href="#" 
                            target="_blank" 
                            class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-sm transition"
                        >
                            Print SKU
                        </a>
                        <a 
                            id="detailMutationsLink" 
                            href="#" 
                            class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-sm transition"
                        >
                            Mutasi
                        </a>
                    </div>

                    <!-- Recent Stock Movements for Scanned Item -->
                    <div class="pt-2">
                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            5 Mutasi Terakhir Barang Ini
                        </div>
                        <div class="overflow-x-auto border border-slate-900/10 rounded-2xl bg-white">
                            <table class="w-full text-left text-xs text-slate-800">
                                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-900/10">
                                    <tr>
                                        <th class="px-4 py-2.5">Waktu</th>
                                        <th class="px-4 py-2.5">Jenis</th>
                                        <th class="px-4 py-2.5">Jumlah</th>
                                        <th class="px-4 py-2.5">Sisa</th>
                                        <th class="px-4 py-2.5">PIC</th>
                                    </tr>
                                </thead>
                                <tbody id="detailMovementsTable" class="divide-y divide-slate-900/10 font-mono">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right 1 Column: Scanned Item Quick Info & Session Activity Log -->
        <div class="space-y-4">
            
            <!-- Quick Last Scanned Card -->
            <div id="lastScannedQuickCard" class="apple-glass-card rounded-3xl p-5 border border-slate-900/10 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-900/10 pb-2.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Barang Terakhir Dipindai</span>
                    <span id="lastScannedTime" class="text-[10px] font-mono text-slate-400">READY</span>
                </div>
                <div id="lastScannedContent" class="text-xs text-slate-400 py-3 text-center">
                    -
                </div>
            </div>

            <!-- Live Session Activity Log -->
            <div class="apple-glass-card rounded-3xl p-5 flex flex-col h-full min-h-[400px]">
                <div class="flex items-center justify-between border-b border-slate-900/10 pb-3 mb-3">
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Aktivitas Sesi Ini</h4>
                        <span id="sessionLogCount" class="text-[11px] text-slate-500 font-semibold">0 aktivitas</span>
                    </div>
                    <button 
                        type="button" 
                        onclick="clearSessionLog()" 
                        class="text-[11px] text-slate-500 hover:text-slate-900 font-semibold underline"
                    >
                        Bersihkan
                    </button>
                </div>

                <div id="sessionLogList" class="flex-1 overflow-y-auto max-h-[480px] space-y-2 pr-1">
                    <div id="emptySessionLogPlaceholder" class="py-12 text-center text-xs text-slate-400">
                        Belum ada aktivitas mutasi.
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Modal Pop-up Konfirmasi Mutasi & Tanda Tangan (Iconless, Textless) -->
<div 
    id="confirmMutationModal" 
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
    onclick="handleBackdropClick(event)"
>
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-5 animate-in fade-in zoom-in duration-150">
        
        <!-- Header Modal (Iconless) -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 id="modalConfirmationTitle" class="text-base font-bold text-slate-900">Konfirmasi Mutasi</h3>
                <span id="modalModeBadge" class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                    Barang Keluar (-)
                </span>
            </div>
            <button 
                type="button" 
                onclick="closeConfirmModal()" 
                class="px-2.5 py-1 rounded-xl text-xs font-semibold text-slate-500 hover:bg-slate-100 transition"
            >
                Tutup
            </button>
        </div>

        <!-- Ringkasan Mutasi -->
        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
            <span class="font-semibold text-slate-600">Total Mutasi</span>
            <span id="modalSummaryStats" class="font-bold font-mono text-slate-900">0 Produk (0 Pcs)</span>
        </div>

        <!-- Form Inputs -->
        <div class="space-y-4 text-xs">
            <!-- Standard Mutasi PIC Container (Mode Out & In) -->
            <div id="modalStandardPicContainer">
                <label for="modalBatchPic" class="block font-semibold text-slate-700 mb-1">
                    Petugas / PIC
                </label>
                <input 
                    type="text" 
                    id="modalBatchPic" 
                    value="" 
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 font-medium text-xs focus:border-slate-900 shadow-2xs"
                >
            </div>

            <!-- Loan Specific Fields Container (Mode Loan) -->
            <div id="modalLoanFieldsContainer" class="hidden space-y-3">
                <div>
                    <label for="modalBorrowerName" class="block font-semibold text-slate-700 mb-1">
                        Nama Peminjam / PIC <span class="text-slate-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="modalBorrowerName" 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 font-medium text-xs focus:border-slate-900 shadow-2xs"
                    >
                </div>

                <div>
                    <label for="modalLoanPurpose" class="block font-semibold text-slate-700 mb-1">
                        Keperluan Peminjaman <span class="text-slate-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="modalLoanPurpose" 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 font-medium text-xs focus:border-slate-900 shadow-2xs"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="modalLoanDate" class="block font-semibold text-slate-700 mb-1">
                            Tanggal Pinjam <span class="text-slate-400">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="modalLoanDate" 
                            value="{{ date('Y-m-d') }}" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 font-medium text-xs focus:border-slate-900 shadow-2xs"
                        >
                    </div>
                    <div>
                        <label for="modalExpectedReturnDate" class="block font-semibold text-slate-700 mb-1">
                            Estimasi Tanggal Kembali
                        </label>
                        <input 
                            type="date" 
                            id="modalExpectedReturnDate" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 font-medium text-xs focus:border-slate-900 shadow-2xs"
                        >
                    </div>
                </div>
            </div>

            <!-- Catatan Mutasi / Surat Jalan (Hanya Tampil di Mode Keluar & Masuk) -->
            <div id="modalBatchNotesContainer">
                <label for="modalBatchNotes" class="block font-semibold text-slate-700 mb-1">
                    Catatan Mutasi / Surat Jalan
                </label>
                <input 
                    type="text" 
                    id="modalBatchNotes" 
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 font-medium text-xs focus:border-slate-900 shadow-2xs"
                >
            </div>

            <!-- Tanda Tangan Digital -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block font-semibold text-slate-700">
                        Tanda Tangan
                    </label>
                    <button 
                        type="button" 
                        onclick="clearModalSignature()" 
                        class="text-[11px] font-bold text-slate-500 hover:text-slate-900 transition"
                    >
                        Hapus Tanda Tangan
                    </button>
                </div>
                <div class="p-2 rounded-2xl bg-slate-50 border border-slate-300">
                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                        <canvas id="modalSignatureCanvas" width="460" height="150" class="w-full h-[150px] cursor-crosshair touch-none bg-white block"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Action Buttons (Iconless) -->
        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <button 
                type="button" 
                onclick="closeConfirmModal()" 
                class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
            >
                Batal
            </button>
            <button 
                type="button" 
                id="btnSubmitModalBatch" 
                onclick="executeBatchMutation()" 
                class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 disabled:bg-slate-300 text-white text-xs font-bold shadow-md transition"
            >
                <span id="btnSubmitModalBatchText">Proses Mutasi</span>
            </button>
        </div>

    </div>
</div>

<!-- Audio Feedback & Scanner Processing Script -->
<script>
    let currentMode = 'out'; // Default to 'out' (Barang Keluar)
    let soundEnabled = true;
    let batchItems = []; // Staging Cart: [{ id, name, sku, category, current_stock, unit, unit_price, quantity }]
    let activeLookupItem = null;
    let sessionLogs = [];

    // Web Audio Synthesizer (Zero asset dependency)
    let audioCtx = null;
    function getAudioContext() {
        if (!audioCtx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                audioCtx = new AudioContext();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    function playBeepSuccess() {
        if (!soundEnabled) return;
        try {
            const ctx = getAudioContext();
            if (!ctx) return;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime); // High pitch crisp beep
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.12);
        } catch (e) {}
    }

    function playBeepBatchDone() {
        if (!soundEnabled) return;
        try {
            const ctx = getAudioContext();
            if (!ctx) return;
            const now = ctx.currentTime;
            
            // Double pleasant chime
            [523.25, 659.25, 783.99].forEach((freq, idx) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, now + idx * 0.08);
                gain.gain.setValueAtTime(0.15, now + idx * 0.08);
                gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.08 + 0.2);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now + idx * 0.08);
                osc.stop(now + idx * 0.08 + 0.2);
            });
        } catch (e) {}
    }

    function playBeepError() {
        if (!soundEnabled) return;
        try {
            const ctx = getAudioContext();
            if (!ctx) return;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(260, ctx.currentTime); // Low pitch error buzz
            gain.gain.setValueAtTime(0.18, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.25);
        } catch (e) {}
    }

    function toggleSound() {
        soundEnabled = !soundEnabled;
        const text = document.getElementById('soundStatusText');
        if (text) {
            text.innerText = soundEnabled ? 'Aktif' : 'Mati';
            text.className = soundEnabled ? 'font-bold text-slate-900' : 'font-bold text-slate-400';
        }
    }

    function setScanMode(mode) {
        currentMode = mode;
        ['out', 'in', 'loan', 'lookup'].forEach(m => {
            const btn = document.getElementById(`btn-mode-${m}`);
            if (btn) {
                if (m === mode) {
                    btn.className = 'flex-1 sm:flex-initial py-1.5 px-3.5 rounded-lg text-xs font-bold transition bg-slate-900 text-white text-center shadow-xs';
                } else {
                    btn.className = 'flex-1 sm:flex-initial py-1.5 px-3.5 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center';
                }
            }
        });

        const cartSec = document.getElementById('batchCartSection');
        const lookupSec = document.getElementById('lookupResultSection');
        const titleEl = document.getElementById('batchCartTitle');
        const badgeEl = document.getElementById('batchModeBadge');

        if (mode === 'lookup') {
            if (cartSec) cartSec.classList.add('hidden');
            if (lookupSec) lookupSec.classList.remove('hidden');
        } else {
            if (cartSec) cartSec.classList.remove('hidden');
            if (lookupSec) lookupSec.classList.add('hidden');

            if (mode === 'out') {
                if (titleEl) titleEl.innerText = 'Daftar Antrean Barang Keluar';
                if (badgeEl) {
                    badgeEl.innerText = 'Barang Keluar (-)';
                    badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white';
                }
            } else if (mode === 'loan') {
                if (titleEl) titleEl.innerText = 'Daftar Antrean Pinjam Barang';
                if (badgeEl) {
                    badgeEl.innerText = 'Pinjam Barang (-)';
                    badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-white';
                }
            } else {
                if (titleEl) titleEl.innerText = 'Daftar Antrean Barang Masuk';
                if (badgeEl) {
                    badgeEl.innerText = 'Barang Masuk (+)';
                    badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-white';
                }
            }
            renderBatchCart();
        }

        focusBarcodeInput();
    }

    function focusBarcodeInput() {
        const input = document.getElementById('barcodeInput');
        if (!input) return;

        // Jangan curi fokus jika modal konfirmasi terbuka
        const modal = document.getElementById('confirmMutationModal');
        if (modal && !modal.classList.contains('hidden')) {
            return;
        }

        // Jangan curi fokus jika admin sedang aktif mengetik di input kuantitas barang
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        const activeClass = document.activeElement ? (document.activeElement.className || '') : '';
        if (activeTag === 'input' && activeClass.includes('cart-qty-input')) {
            return;
        }

        if (document.activeElement !== input) {
            input.focus();
        }
    }

    function showFeedback(message, isError = false) {
        const box = document.getElementById('scanFeedbackBox');
        if (!box) return;

        box.innerText = message;
        if (isError) {
            box.className = 'rounded-xl p-3 text-xs font-semibold border border-slate-300 bg-slate-100 text-slate-800';
            playBeepError();
        } else {
            box.className = 'rounded-xl p-3 text-xs font-semibold border border-slate-900 bg-slate-900 text-white';
        }
        box.classList.remove('hidden');
    }

    // Global Standby Scanner Listener (Admin tidak perlu cape-cape klik search bar)
    window.addEventListener('keydown', (e) => {
        // 1. Jika modal konfirmasi terbuka, biarkan interaksi modal & tangani Escape
        const modal = document.getElementById('confirmMutationModal');
        if (modal && !modal.classList.contains('hidden')) {
            if (e.key === 'Escape') {
                closeConfirmModal();
            }
            return;
        }

        // 2. Jika admin sedang aktif mengetik kuantitas di baris keranjang
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        const activeClass = document.activeElement ? (document.activeElement.className || '') : '';

        if (activeTag === 'input' && activeClass.includes('cart-qty-input')) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.activeElement.blur();
                focusBarcodeInput();
            }
            return;
        }

        const input = document.getElementById('barcodeInput');
        if (!input) return;

        // 3. Tombol Enter langsung memproses kode dari scanner / input
        if (e.key === 'Enter') {
            e.preventDefault();
            const code = input.value.trim();
            if (code.length > 0) {
                processScannedCode(code);
                input.value = '';
            }
            focusBarcodeInput();
            return;
        }

        // 4. Standby otomatis: ketikan scanner/admin langsung dialihkan ke search bar tanpa perlu diklik
        if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
            if (document.activeElement !== input) {
                input.focus();
                input.value += e.key;
                e.preventDefault();
            }
        }
    });

    // Otomatis standby kembali ke search bar saat admin klik area kosong manapun
    document.addEventListener('click', (e) => {
        const target = e.target;
        if (!target) return;
        if (target.closest('#confirmMutationModal') || target.closest('.cart-qty-input') || target.closest('button') || target.closest('a') || target.closest('input')) {
            return;
        }
        focusBarcodeInput();
    });

    // Otomatis standby saat tab/jendela browser aktif kembali
    window.addEventListener('focus', () => {
        focusBarcodeInput();
    });

    function handleManualSubmit(e) {
        e.preventDefault();
        const input = document.getElementById('barcodeInput');
        if (!input) return;
        const code = input.value.trim();
        if (code) {
            processScannedCode(code);
            input.value = '';
        }
    }

    function updateScannerPulse(status) {
        const livePulse = document.getElementById('scannerLivePulse');
        if (!livePulse) return;
        if (status === 'BUSY') {
            livePulse.innerText = 'BUSY...';
            livePulse.classList.remove('bg-slate-100', 'text-slate-700', 'border-slate-300');
            livePulse.classList.add('bg-amber-100', 'text-amber-800', 'border-amber-300');
        } else {
            livePulse.innerText = 'STANDBY';
            livePulse.classList.remove('bg-amber-100', 'text-amber-800', 'border-amber-300');
            livePulse.classList.add('bg-slate-100', 'text-slate-700', 'border-slate-300');
        }
    }

    async function processScannedCode(rawCode) {
        const submitBtn = document.getElementById('submitScanBtn');
        if (submitBtn) submitBtn.disabled = true;
        updateScannerPulse('BUSY');

        // Check for Multiplier Syntax: e.g. "50*SKU123" or "50xSKU123" or "50 * SKU123"
        let scannedQty = 1;
        let cleanCode = rawCode.trim();

        const multiplierMatch = cleanCode.match(/^(\d+)\s*[\*xX]\s*(.+)$/);
        if (multiplierMatch) {
            scannedQty = parseInt(multiplierMatch[1], 10);
            cleanCode = multiplierMatch[2].trim();
        }

        try {
            // Lookup item by scanned code
            const lookupRes = await fetch("{{ route('stin.scanner.lookup') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ code: cleanCode })
            });

            const lookupData = await lookupRes.json();

            if (!lookupRes.ok || !lookupData.success) {
                showFeedback(lookupData.message || `Barang '${cleanCode}' tidak ditemukan di gudang.`, true);
                if (submitBtn) submitBtn.disabled = false;
                updateScannerPulse('STANDBY');
                focusBarcodeInput();
                return;
            }

            const item = lookupData.item;
            renderLastScannedQuickCard(item, scannedQty);

            if (currentMode === 'lookup') {
                activeLookupItem = item;
                renderLookupDetails(item, lookupData.recent_movements || []);
                showFeedback(`Ditemukan: ${item.name} (${item.sku}) — Stok: ${item.current_stock} ${item.unit}.`, false);
                playBeepSuccess();
            } else {
                // Add or increment item in batch cart
                addItemToBatch(item, scannedQty);
                playBeepSuccess();
                showFeedback(`+ Masuk daftar: ${item.name} (+${scannedQty} ${item.unit}). Atur kuantitas bila perlu.`, false);
            }

        } catch (err) {
            showFeedback('Terjadi kendala jaringan saat memproses scan.', true);
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            updateScannerPulse('STANDBY');
            focusBarcodeInput();
        }
    }

    // Cart Management
    function addItemToBatch(item, qty) {
        const existing = batchItems.find(i => i.id === item.id);
        if (existing) {
            existing.quantity += qty;
        } else {
            batchItems.push({
                id: item.id,
                name: item.name,
                sku: item.sku,
                category: item.category || '-',
                current_stock: Number(item.current_stock),
                unit: item.unit || 'pcs',
                unit_price: Number(item.unit_price || 0),
                quantity: qty
            });
        }
        renderBatchCart();
    }

    function addLookupItemToCart(targetMode) {
        if (!activeLookupItem) return;
        setScanMode(targetMode);
        addItemToBatch(activeLookupItem, 1);
        playBeepSuccess();
        showFeedback(`Barang '${activeLookupItem.name}' dimasukkan ke daftar antrean.`, false);
    }

    function updateItemQty(id, value) {
        const item = batchItems.find(i => i.id === id);
        if (!item) return;

        let num = parseInt(value, 10);
        if (isNaN(num) || num < 1) {
            num = 1;
        }
        item.quantity = num;
        renderBatchCart(false); // Don't re-render entire inputs to avoid losing input focus
    }

    function removeItemFromBatch(id) {
        batchItems = batchItems.filter(i => i.id !== id);
        renderBatchCart();
        focusBarcodeInput();
    }

    function clearBatchList() {
        if (batchItems.length === 0) return;
        if (confirm('Apakah Anda yakin ingin mengosongkan seluruh daftar antrean ini?')) {
            batchItems = [];
            renderBatchCart();
            focusBarcodeInput();
        }
    }

    function renderBatchCart(fullRender = true) {
        const emptyPlc = document.getElementById('emptyCartPlaceholder');
        const tableContainer = document.getElementById('cartTableContainer');
        const tbody = document.getElementById('cartTableBody');
        const countBadge = document.getElementById('batchCountBadge');
        const qtyBadge = document.getElementById('batchTotalQtyBadge');
        const summaryText = document.getElementById('batchSummaryText');
        const openModalBtn = document.getElementById('btnOpenConfirmModal');
        const openModalText = document.getElementById('btnOpenConfirmModalText');

        const totalKinds = batchItems.length;
        const totalQty = batchItems.reduce((acc, curr) => acc + (parseInt(curr.quantity, 10) || 0), 0);
        const actionLabel = currentMode === 'in' ? 'Barang Masuk' : 'Barang Keluar';

        if (countBadge) countBadge.innerText = `${totalKinds} Jenis Produk`;
        if (qtyBadge) qtyBadge.innerText = `${totalQty.toLocaleString('id-ID')} Total Qty`;
        if (summaryText) summaryText.innerText = `${totalKinds} Jenis Produk (${totalQty.toLocaleString('id-ID')} Total Qty)`;

        let hasError = false;

        if (totalKinds === 0) {
            if (emptyPlc) emptyPlc.classList.remove('hidden');
            if (tableContainer) tableContainer.classList.add('hidden');
            if (openModalBtn) openModalBtn.disabled = true;
            if (openModalText) openModalText.innerText = 'Konfirmasi Mutasi';
            return;
        }

        if (emptyPlc) emptyPlc.classList.add('hidden');
        if (tableContainer) tableContainer.classList.remove('hidden');

        // Check if any item exceeds stock in 'out' or 'loan' mode
        if (currentMode === 'out' || currentMode === 'loan') {
            for (const it of batchItems) {
                if (it.quantity > it.current_stock) {
                    hasError = true;
                    break;
                }
            }
        }

        if (openModalBtn) {
            openModalBtn.disabled = hasError;
        }

        if (openModalText) {
            if (hasError) {
                openModalText.innerText = 'Kuantitas Melebihi Stok Gudang';
            } else {
                openModalText.innerText = `Konfirmasi Mutasi (${totalKinds} Produk, ${totalQty.toLocaleString('id-ID')} Pcs)`;
            }
        }

        if (!fullRender) {
            // Update only badges and warnings without rebuilding DOM inputs
            batchItems.forEach(item => {
                const isExceed = (currentMode === 'out' || currentMode === 'loan') && item.quantity > item.current_stock;
                const warnEl = document.getElementById(`warn_qty_${item.id}`);
                const inputEl = document.getElementById(`input_qty_${item.id}`);
                if (warnEl) {
                    if (isExceed) {
                        warnEl.classList.remove('hidden');
                    } else {
                        warnEl.classList.add('hidden');
                    }
                }
                if (inputEl) {
                    if (isExceed) {
                        inputEl.className = 'w-20 text-center font-mono font-bold text-sm px-2 py-1.5 rounded-xl border border-slate-400 text-slate-900 bg-slate-100 focus:border-slate-900 cart-qty-input';
                    } else {
                        inputEl.className = 'w-20 text-center font-mono font-bold text-sm px-2 py-1.5 rounded-xl border border-slate-300 text-slate-900 bg-white focus:border-slate-900 cart-qty-input';
                    }
                }
            });
            return;
        }

        if (!tbody) return;

        tbody.innerHTML = batchItems.map(item => {
            const isExceed = (currentMode === 'out' || currentMode === 'loan') && item.quantity > item.current_stock;

            return `
                <tr id="cartRow_${item.id}" class="hover:bg-slate-50/80 transition">
                    <td class="px-4 py-3">
                        <div class="font-bold text-slate-900 text-xs">${item.name}</div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-mono text-[11px] text-slate-500 font-bold">${item.sku}</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-semibold">${item.category}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-mono font-bold text-xs text-slate-800">${item.current_stock.toLocaleString('id-ID')}</span>
                        <span class="text-[10px] text-slate-500 block">${item.unit}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center">
                            <input 
                                type="number" 
                                id="input_qty_${item.id}"
                                min="1" 
                                value="${item.quantity}" 
                                oninput="updateItemQty(${item.id}, this.value)" 
                                class="w-20 text-center font-mono font-bold text-sm px-2 py-1.5 rounded-xl border ${isExceed ? 'border-slate-400 text-slate-900 bg-slate-100 focus:border-slate-900' : 'border-slate-300 text-slate-900 bg-white focus:border-slate-900'} shadow-2xs cart-qty-input"
                            >
                        </div>
                        <div id="warn_qty_${item.id}" class="${isExceed ? '' : 'hidden'} text-[10px] text-slate-700 font-bold text-center mt-1">
                            Melebihi sisa stok (${item.current_stock} ${item.unit})!
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-slate-600">
                        ${item.unit}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button type="button" onclick="removeItemFromBatch(${item.id})" class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                            Hapus
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Modal Confirmation & Digital Signature Logic
    let modalCanvasInitialized = false;
    let hasModalSignature = false;
    let modalCtx = null;

    function openConfirmModal() {
        if (batchItems.length === 0) {
            showFeedback('Daftar antrean masih kosong. Pindai barcode barang terlebih dahulu.', true);
            return;
        }

        // Validate stock in out or loan mode
        if (currentMode === 'out' || currentMode === 'loan') {
            for (const it of batchItems) {
                if (it.quantity > it.current_stock) {
                    showFeedback(`Barang '${it.name}' diminta ${it.quantity} ${it.unit}, tapi stok hanya ${it.current_stock} ${it.unit}.`, true);
                    return;
                }
            }
        }

        const modal = document.getElementById('confirmMutationModal');
        const modalTitle = document.getElementById('modalConfirmationTitle');
        const modalModeBadge = document.getElementById('modalModeBadge');
        const modalSummaryStats = document.getElementById('modalSummaryStats');
        const submitText = document.getElementById('btnSubmitModalBatchText');
        const stdPicContainer = document.getElementById('modalStandardPicContainer');
        const loanFieldsContainer = document.getElementById('modalLoanFieldsContainer');
        const batchNotesContainer = document.getElementById('modalBatchNotesContainer');

        const totalKinds = batchItems.length;
        const totalQty = batchItems.reduce((acc, curr) => acc + (parseInt(curr.quantity, 10) || 0), 0);

        if (currentMode === 'loan') {
            if (modalTitle) modalTitle.innerText = 'Konfirmasi Pinjam Barang';
            if (stdPicContainer) stdPicContainer.classList.add('hidden');
            if (loanFieldsContainer) loanFieldsContainer.classList.remove('hidden');
            if (batchNotesContainer) batchNotesContainer.classList.add('hidden');
            if (modalModeBadge) {
                modalModeBadge.innerText = 'Pinjam Barang (-)';
                modalModeBadge.className = 'inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white';
            }
            if (submitText) {
                submitText.innerText = 'Proses Peminjaman Barang';
            }
        } else {
            if (modalTitle) modalTitle.innerText = 'Konfirmasi Mutasi';
            if (stdPicContainer) stdPicContainer.classList.remove('hidden');
            if (loanFieldsContainer) loanFieldsContainer.classList.add('hidden');
            if (batchNotesContainer) batchNotesContainer.classList.remove('hidden');
            const actionLabel = currentMode === 'in' ? 'Barang Masuk (+)' : 'Barang Keluar (-)';
            if (modalModeBadge) {
                modalModeBadge.innerText = actionLabel;
                modalModeBadge.className = 'inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white';
            }
            if (submitText) {
                submitText.innerText = `Proses Mutasi ${currentMode === 'in' ? 'Masuk' : 'Keluar'}`;
            }
        }

        if (modalSummaryStats) {
            modalSummaryStats.innerText = `${totalKinds} Produk (${totalQty.toLocaleString('id-ID')} Total Qty)`;
        }

        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                initModalSignatureCanvas();
                if (currentMode === 'loan') {
                    document.getElementById('modalBorrowerName')?.focus();
                } else {
                    document.getElementById('modalBatchPic')?.focus();
                }
            }, 50);
        }
    }

    function closeConfirmModal() {
        const modal = document.getElementById('confirmMutationModal');
        if (modal) {
            modal.classList.add('hidden');
        }
        focusBarcodeInput();
    }

    function handleBackdropClick(event) {
        if (event.target && event.target.id === 'confirmMutationModal') {
            closeConfirmModal();
        }
    }

    function initModalSignatureCanvas() {
        const canvas = document.getElementById('modalSignatureCanvas');
        if (!canvas) return;

        const rect = canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        const width = rect.width || 460;
        const height = rect.height || 150;

        canvas.width = width * dpr;
        canvas.height = height * dpr;

        modalCtx = canvas.getContext('2d');
        modalCtx.scale(dpr, dpr);
        modalCtx.strokeStyle = '#0f172a';
        modalCtx.lineWidth = 2.5;
        modalCtx.lineCap = 'round';
        modalCtx.lineJoin = 'round';

        // Clear canvas
        modalCtx.clearRect(0, 0, width, height);
        hasModalSignature = false;

        if (modalCanvasInitialized) return;
        modalCanvasInitialized = true;

        let isDrawing = false;

        function getCoords(e) {
            const r = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - r.left,
                y: clientY - r.top
            };
        }

        canvas.addEventListener('mousedown', (e) => {
            isDrawing = true;
            hasModalSignature = true;
            const coords = getCoords(e);
            modalCtx.beginPath();
            modalCtx.moveTo(coords.x, coords.y);
            e.preventDefault();
        });

        canvas.addEventListener('mousemove', (e) => {
            if (!isDrawing) return;
            const coords = getCoords(e);
            modalCtx.lineTo(coords.x, coords.y);
            modalCtx.stroke();
            e.preventDefault();
        });

        const stopDrawing = () => { isDrawing = false; };
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        canvas.addEventListener('touchstart', (e) => {
            isDrawing = true;
            hasModalSignature = true;
            const coords = getCoords(e);
            modalCtx.beginPath();
            modalCtx.moveTo(coords.x, coords.y);
            e.preventDefault();
        }, { passive: false });

        canvas.addEventListener('touchmove', (e) => {
            if (!isDrawing) return;
            const coords = getCoords(e);
            modalCtx.lineTo(coords.x, coords.y);
            modalCtx.stroke();
            e.preventDefault();
        }, { passive: false });

        canvas.addEventListener('touchend', stopDrawing);
    }

    function clearModalSignature() {
        const canvas = document.getElementById('modalSignatureCanvas');
        if (canvas && modalCtx) {
            const rect = canvas.getBoundingClientRect();
            modalCtx.clearRect(0, 0, rect.width || 460, rect.height || 150);
            hasModalSignature = false;
        }
    }

    // Submit Final Batch Mutation with Signature
    async function executeBatchMutation() {
        if (batchItems.length === 0) {
            showFeedback('Daftar antrean masih kosong. Pindai barcode barang terlebih dahulu.', true);
            closeConfirmModal();
            return;
        }

        // Validate stock in out or loan mode
        if (currentMode === 'out' || currentMode === 'loan') {
            for (const it of batchItems) {
                if (it.quantity > it.current_stock) {
                    showFeedback(`Barang '${it.name}' diminta ${it.quantity} ${it.unit}, tapi stok hanya ${it.current_stock} ${it.unit}.`, true);
                    closeConfirmModal();
                    return;
                }
            }
        }

        const submitBtn = document.getElementById('btnSubmitModalBatch');
        const submitText = document.getElementById('btnSubmitModalBatchText');
        const notes = document.getElementById('modalBatchNotes')?.value?.trim() || '';

        let signatureData = null;
        if (hasModalSignature) {
            const canvas = document.getElementById('modalSignatureCanvas');
            if (canvas) {
                signatureData = canvas.toDataURL('image/png');
            }
        }

        // Jalur Eksekusi Mode Pinjam Barang (-)
        if (currentMode === 'loan') {
            const borrowerName = document.getElementById('modalBorrowerName')?.value?.trim() || '';
            const purpose = document.getElementById('modalLoanPurpose')?.value?.trim() || '';
            const loanDate = document.getElementById('modalLoanDate')?.value || new Date().toISOString().split('T')[0];
            const expectedReturnDate = document.getElementById('modalExpectedReturnDate')?.value || null;

            if (!borrowerName) {
                showFeedback('Nama peminjam / PIC wajib diisi.', true);
                document.getElementById('modalBorrowerName')?.focus();
                return;
            }

            if (!purpose) {
                showFeedback('Keperluan peminjaman wajib diisi.', true);
                document.getElementById('modalLoanPurpose')?.focus();
                return;
            }

            if (submitBtn) submitBtn.disabled = true;
            if (submitText) submitText.innerText = 'Memproses Peminjaman...';

            try {
                const payload = {
                    borrower_name: borrowerName,
                    purpose: purpose,
                    loan_date: loanDate,
                    expected_return_date: expectedReturnDate,
                    signature_data: signatureData,
                    items: batchItems.map(i => ({
                        warehouse_item_id: i.id,
                        quantity: i.quantity
                    }))
                };

                const res = await fetch("{{ route('stin.loans.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    const errorMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Gagal memproses peminjaman barang.');
                    showFeedback(errorMsg, true);
                    if (submitBtn) submitBtn.disabled = false;
                    if (submitText) submitText.innerText = 'Proses Peminjaman Barang';
                    return;
                }

                // Success
                playBeepBatchDone();
                showFeedback(data.message, false);

                if (data.log) {
                    addSessionLog(data.log);
                }

                // Clear modal loan inputs
                const nameInput = document.getElementById('modalBorrowerName');
                if (nameInput) nameInput.value = '';
                const purposeInput = document.getElementById('modalLoanPurpose');
                if (purposeInput) purposeInput.value = '';
                const expectedDateInput = document.getElementById('modalExpectedReturnDate');
                if (expectedDateInput) expectedDateInput.value = '';

                clearModalSignature();
                closeConfirmModal();

                // Clear batch cart
                batchItems = [];
                renderBatchCart();

            } catch (e) {
                showFeedback('Terjadi kesalahan jaringan saat menyimpan peminjaman barang.', true);
            } finally {
                if (submitBtn) submitBtn.disabled = false;
                if (submitText) submitText.innerText = 'Proses Peminjaman Barang';
                focusBarcodeInput();
            }
            return;
        }

        // Jalur Eksekusi Mode Standar: Barang Keluar & Barang Masuk
        const picName = document.getElementById('modalBatchPic')?.value?.trim() || '';

        if (submitBtn) submitBtn.disabled = true;
        if (submitText) submitText.innerText = 'Memproses Transaksi...';

        try {
            const payload = {
                action_type: currentMode,
                items: batchItems.map(i => ({
                    warehouse_item_id: i.id,
                    quantity: i.quantity
                })),
                pic_name: picName,
                notes: notes,
                signature_data: signatureData
            };

            const res = await fetch("{{ route('stin.scanner.batch-action') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                showFeedback(data.message || 'Gagal memproses mutasi stok.', true);
                if (submitBtn) submitBtn.disabled = false;
                if (submitText) submitText.innerText = `Proses Mutasi ${currentMode === 'in' ? 'Masuk' : 'Keluar'}`;
                return;
            }

            // Success!
            playBeepBatchDone();
            showFeedback(data.message, false);

            if (data.log) {
                addSessionLog(data.log);
            }

            // Clear modal inputs
            const notesInput = document.getElementById('modalBatchNotes');
            if (notesInput) notesInput.value = '';
            clearModalSignature();
            closeConfirmModal();

            // Clear batch cart
            batchItems = [];
            renderBatchCart();

        } catch (e) {
            showFeedback('Terjadi kesalahan jaringan saat menyimpan batch mutasi.', true);
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            focusBarcodeInput();
        }
    }

    function renderLastScannedQuickCard(item, qty) {
        const timeEl = document.getElementById('lastScannedTime');
        const contentEl = document.getElementById('lastScannedContent');
        if (timeEl) {
            timeEl.innerText = new Date().toLocaleTimeString('id-ID', { hour12: false });
        }
        if (contentEl) {
            contentEl.innerHTML = `
                <div class="space-y-1.5 text-left">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-[11px] font-bold text-slate-900">${item.sku}</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-slate-100 text-slate-700">+${qty} ${item.unit}</span>
                    </div>
                    <div class="font-bold text-slate-800 text-xs">${item.name}</div>
                    <div class="text-[11px] text-slate-500 flex items-center justify-between pt-1">
                        <span>Sisa Stok: <strong class="text-slate-900 font-mono">${Number(item.current_stock).toLocaleString('id-ID')} ${item.unit}</strong></span>
                        <span class="font-semibold text-slate-700">${item.formatted_price || ''}</span>
                    </div>
                </div>
            `;
        }
    }

    function renderLookupDetails(item, movements) {
        document.getElementById('emptyLookupPlaceholder')?.classList.add('hidden');
        document.getElementById('activeLookupDetails')?.classList.remove('hidden');

        document.getElementById('detailCategory').innerText = item.category || '-';
        document.getElementById('detailName').innerText = item.name;
        document.getElementById('detailSku').innerText = item.sku;
        document.getElementById('detailStock').innerText = Number(item.current_stock).toLocaleString('id-ID');
        document.getElementById('detailUnit').innerText = item.unit || 'pcs';
        document.getElementById('detailPrice').innerText = item.formatted_price;

        const loanedBadge = document.getElementById('detailLoanedBadge');
        const loanedQty = document.getElementById('detailLoanedQty');
        if (item.currently_loaned && item.currently_loaned > 0) {
            if (loanedBadge) loanedBadge.classList.remove('hidden');
            if (loanedQty) loanedQty.innerText = `${item.currently_loaned} ${item.unit || 'pcs'}`;
        } else {
            if (loanedBadge) loanedBadge.classList.add('hidden');
        }

        document.getElementById('detailPrintLink').href = item.print_sku_url;
        document.getElementById('detailMutationsLink').href = item.mutations_url;

        // Render recent movements table
        const tbody = document.getElementById('detailMovementsTable');
        if (tbody) {
            if (movements.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-4 text-center text-slate-400">Belum ada riwayat mutasi</td></tr>`;
            } else {
                tbody.innerHTML = movements.map(m => `
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 text-[11px] text-slate-600">${m.date}</td>
                        <td class="px-4 py-2 font-bold text-[11px] text-slate-900">${m.type_label}</td>
                        <td class="px-4 py-2 font-bold text-xs text-slate-900">${m.type === 'in' ? '+' : (m.type === 'out' ? '-' : '')}${Number(m.quantity).toLocaleString('id-ID')}</td>
                        <td class="px-4 py-2 text-xs text-slate-700">${Number(m.stock_after).toLocaleString('id-ID')}</td>
                        <td class="px-4 py-2 text-[11px] text-slate-600">${m.pic_name || '-'}</td>
                    </tr>
                `).join('');
            }
        }
    }

    function addSessionLog(log) {
        sessionLogs.unshift(log);
        renderSessionLogs();
    }

    function renderSessionLogs() {
        const container = document.getElementById('sessionLogList');
        const countBadge = document.getElementById('sessionLogCount');
        if (!container) return;

        if (countBadge) {
            countBadge.innerText = `${sessionLogs.length} aktivitas`;
        }

        if (sessionLogs.length === 0) {
            container.innerHTML = `<div id="emptySessionLogPlaceholder" class="py-10 text-center text-xs text-slate-400">Belum ada aktivitas mutasi.</div>`;
            return;
        }

        container.innerHTML = sessionLogs.map(log => {
            let actionBadge = '';
            if (log.action_type === 'in') {
                actionBadge = `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-900 text-white">+${log.quantity} ${log.unit || 'pcs'} Masuk</span>`;
            } else if (log.action_type === 'loan') {
                actionBadge = `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold border border-slate-300 bg-slate-100 text-slate-900">-${log.quantity} ${log.unit || 'pcs'} Dipinjam</span>`;
            } else if (log.action_type === 'out') {
                actionBadge = `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold border border-slate-300 bg-white text-slate-900">-${log.quantity} ${log.unit || 'pcs'} Keluar</span>`;
            } else {
                actionBadge = `<span class="px-1.5 py-0.5 rounded text-[10px] font-semibold text-slate-600 bg-slate-100">Cek Info</span>`;
            }

            return `
                <div class="p-2.5 rounded-2xl border border-slate-200 bg-white shadow-2xs space-y-1">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="font-mono font-bold text-slate-900">${log.sku}</span>
                        <span class="text-slate-400 font-mono text-[10px]">${log.time}</span>
                    </div>
                    <div class="text-xs font-semibold text-slate-800 truncate">${log.name}</div>
                    <div class="flex items-center justify-between pt-1 text-[10px]">
                        ${actionBadge}
                        <span class="text-slate-500 font-medium truncate max-w-[120px]">PIC: ${log.pic_name || '-'}</span>
                    </div>
                </div>
            `;
        }).join('');
    }

    function clearSessionLog() {
        sessionLogs = [];
        renderSessionLogs();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const initialMode = urlParams.get('mode');
        if (['out', 'in', 'loan', 'lookup'].includes(initialMode)) {
            setScanMode(initialMode);
        } else {
            setScanMode('out');
        }
        renderBatchCart();
        focusBarcodeInput();
    });
</script>
@endsection
