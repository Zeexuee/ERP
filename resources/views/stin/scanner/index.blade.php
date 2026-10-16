@extends('layouts.app', ['title' => 'Sistem Scan Barcode'])

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Sistem Scan Barcode</h2>
            <div class="text-xs text-slate-500 font-medium mt-0.5">
                Pindai barcode 1D atau QR code untuk identifikasi dan mutasi stok cepat.
            </div>
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
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md border border-slate-900/10 space-y-5">
        
        <!-- Mode Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-900/10 pb-4">
            <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                Mode Pemindaian
            </div>
            <div class="inline-flex rounded-xl border border-slate-300 bg-white p-1 max-w-md w-full sm:w-auto">
                <button 
                    type="button" 
                    onclick="setScanMode('lookup')" 
                    id="btn-mode-lookup" 
                    class="flex-1 sm:flex-initial py-1.5 px-4 rounded-lg text-xs font-semibold transition bg-slate-900 text-white text-center"
                >
                    Cek Stok (Lookup)
                </button>
                <button 
                    type="button" 
                    onclick="setScanMode('in')" 
                    id="btn-mode-in" 
                    class="flex-1 sm:flex-initial py-1.5 px-4 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center"
                >
                    Barang Masuk (+)
                </button>
                <button 
                    type="button" 
                    onclick="setScanMode('out')" 
                    id="btn-mode-out" 
                    class="flex-1 sm:flex-initial py-1.5 px-4 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center"
                >
                    Barang Keluar (-)
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
                            placeholder="Arahkan scanner hardware ke sini atau ketik SKU / Barcode..." 
                            class="apple-input w-full rounded-2xl px-4 py-3 text-sm font-mono font-medium border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 focus:border-slate-900 shadow-xs"
                        >
                        <div id="scannerLivePulse" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[10px] font-mono text-slate-400 select-none">
                            READY
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        id="submitScanBtn" 
                        class="px-6 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition whitespace-nowrap"
                    >
                        Proses Scan
                    </button>
                </div>

                <!-- Parameters for Stock In / Out (Visible when mode is 'in' or 'out') -->
                <div id="quickActionParams" class="hidden grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 text-xs">
                    <div>
                        <label for="actionQty" class="block font-semibold text-slate-700 mb-1">
                            Jumlah Per Scan
                        </label>
                        <div class="flex items-center gap-1.5">
                            <input 
                                type="number" 
                                id="actionQty" 
                                min="1" 
                                value="1" 
                                class="w-24 px-3 py-1.5 rounded-xl border border-slate-300 bg-white font-bold text-slate-900 text-xs"
                            >
                            <button type="button" onclick="setQuickQty(1)" class="px-2 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-[11px] font-semibold text-slate-700">1</button>
                            <button type="button" onclick="setQuickQty(5)" class="px-2 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-[11px] font-semibold text-slate-700">5</button>
                            <button type="button" onclick="setQuickQty(10)" class="px-2 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-[11px] font-semibold text-slate-700">10</button>
                        </div>
                    </div>

                    <div>
                        <label for="actionPic" class="block font-semibold text-slate-700 mb-1">
                            Petugas / PIC
                        </label>
                        <input 
                            type="text" 
                            id="actionPic" 
                            value="{{ auth()->user()?->name ?? 'Petugas STIN' }}" 
                            class="w-full px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-slate-900 font-medium text-xs"
                        >
                    </div>

                    <div>
                        <label for="actionNotes" class="block font-semibold text-slate-700 mb-1">
                            Catatan Mutasi
                        </label>
                        <input 
                            type="text" 
                            id="actionNotes" 
                            placeholder="Catatan proses mutasi..." 
                            class="w-full px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-slate-900 font-medium text-xs"
                        >
                    </div>
                </div>
            </form>
        </div>

        <!-- Notification Message Box -->
        <div id="scanFeedbackBox" class="hidden rounded-xl p-3 text-xs font-semibold transition"></div>

    </div>

    <!-- Main Workspace Grid: Scanned Result Card & Activity Log -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Scanned Item Card (Left 2 Columns) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="apple-glass-card rounded-3xl p-6 min-h-[300px] flex flex-col justify-between">
                
                <div id="emptyResultPlaceholder" class="my-auto py-12 text-center text-slate-400">
                    <div class="text-sm font-semibold text-slate-500">Belum Ada Barang Terpindai</div>
                    <div class="text-xs text-slate-400 mt-1">Pindai barcode pada kemasan barang untuk menampilkan data dan stok secara otomatis.</div>
                </div>

                <div id="activeItemDetails" class="hidden space-y-5">
                    
                    <!-- Item Header & Stock Badge -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 border-b border-slate-900/10 pb-4">
                        <div>
                            <span id="detailCategory" class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1"></span>
                            <h3 id="detailName" class="text-lg font-bold text-slate-900"></h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-slate-500 font-medium">SKU:</span>
                                <span id="detailSku" class="font-mono text-xs font-bold text-slate-900"></span>
                            </div>
                        </div>

                        <!-- Current Stock Display -->
                        <div class="bg-white border border-slate-300 rounded-2xl px-5 py-3 text-center sm:text-right shadow-xs">
                            <span class="block text-[10px] uppercase font-bold text-slate-500 tracking-wider">Stok Saat Ini</span>
                            <div class="text-2xl font-bold text-slate-900 font-mono mt-0.5">
                                <span id="detailStock">0</span>
                                <span id="detailUnit" class="text-xs font-normal text-slate-600"></span>
                            </div>
                            <div id="detailPrice" class="text-[11px] font-semibold text-slate-700 mt-0.5"></div>
                        </div>
                    </div>

                    <!-- Direct Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <button 
                            type="button" 
                            onclick="triggerDirectAction('in')" 
                            class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition"
                        >
                            + Tambah Stok Masuk
                        </button>
                        <button 
                            type="button" 
                            onclick="triggerDirectAction('out')" 
                            class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-900 text-xs font-bold shadow-sm transition"
                        >
                            - Kurangi Stok Keluar
                        </button>
                        <a 
                            id="detailPrintLink" 
                            href="#" 
                            target="_blank" 
                            class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-sm transition"
                        >
                            Print SKU Barcode
                        </a>
                        <a 
                            id="detailMutationsLink" 
                            href="#" 
                            class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-sm transition"
                        >
                            Riwayat Mutasi
                        </a>
                        <a 
                            id="detailEditLink" 
                            href="#" 
                            class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-sm transition"
                        >
                            Edit Barang
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
                                    <!-- Diisi secara dinamis -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- Live Session Activity Log (Right 1 Column) -->
        <div class="space-y-4">
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
                        Belum ada aktivitas scan pada sesi ini.
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Audio Feedback & Scanner Processing Script -->
<script>
    let currentMode = 'lookup'; // 'lookup', 'in', 'out'
    let soundEnabled = true;
    let activeItem = null;
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
        } catch (e) {
            // Audio error ignored
        }
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
        } catch (e) {
            // Audio error ignored
        }
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
        ['lookup', 'in', 'out'].forEach(m => {
            const btn = document.getElementById(`btn-mode-${m}`);
            if (btn) {
                if (m === mode) {
                    btn.className = 'flex-1 sm:flex-initial py-1.5 px-4 rounded-lg text-xs font-semibold transition bg-slate-900 text-white text-center';
                } else {
                    btn.className = 'flex-1 sm:flex-initial py-1.5 px-4 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center';
                }
            }
        });

        const quickParams = document.getElementById('quickActionParams');
        if (quickParams) {
            if (mode === 'in' || mode === 'out') {
                quickParams.classList.remove('hidden');
            } else {
                quickParams.classList.add('hidden');
            }
        }

        focusBarcodeInput();
    }

    function setQuickQty(qty) {
        const input = document.getElementById('actionQty');
        if (input) input.value = qty;
        focusBarcodeInput();
    }

    function focusBarcodeInput() {
        const input = document.getElementById('barcodeInput');
        if (input) {
            input.focus();
            input.select();
        }
    }

    function showFeedback(message, isError = false) {
        const box = document.getElementById('scanFeedbackBox');
        if (!box) return;

        box.innerText = message;
        if (isError) {
            box.className = 'rounded-xl p-3 text-xs font-semibold border border-slate-300 bg-slate-100 text-slate-900';
            playBeepError();
        } else {
            box.className = 'rounded-xl p-3 text-xs font-semibold border border-slate-900 bg-slate-900 text-white';
            playBeepSuccess();
        }
        box.classList.remove('hidden');
    }

    // Global Hardware Scanner Listener (captures rapid typing + Enter anywhere on page)
    let scannerBuffer = '';
    let lastKeyTime = Date.now();

    window.addEventListener('keydown', (e) => {
        // If user is actively typing in notes or pic input, let normal typing occur
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        const activeId = document.activeElement ? document.activeElement.id : '';

        if (activeTag === 'input' && (activeId === 'actionNotes' || activeId === 'actionPic' || activeId === 'actionQty')) {
            return;
        }

        const currentTime = Date.now();
        const diff = currentTime - lastKeyTime;
        lastKeyTime = currentTime;

        // Hardware scanners output characters within < 40ms interval
        if (e.key === 'Enter') {
            const input = document.getElementById('barcodeInput');
            const code = (input && document.activeElement === input) ? input.value.trim() : scannerBuffer.trim();

            if (code.length > 0) {
                e.preventDefault();
                processScannedCode(code);
                if (input) input.value = '';
                scannerBuffer = '';
            }
        } else if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
            if (diff > 120) {
                // If pause between keystrokes was slow, reset buffer
                scannerBuffer = '';
            }
            scannerBuffer += e.key;
        }
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

    async function processScannedCode(code) {
        const submitBtn = document.getElementById('submitScanBtn');
        const livePulse = document.getElementById('scannerLivePulse');
        if (submitBtn) submitBtn.disabled = true;
        if (livePulse) livePulse.innerText = 'BUSY...';

        try {
            // First: Lookup item by scanned code
            const lookupRes = await fetch("{{ route('stin.scanner.lookup') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ code: code })
            });

            const lookupData = await lookupRes.json();

            if (!lookupRes.ok || !lookupData.success) {
                showFeedback(lookupData.message || `Barang '${code}' tidak ditemukan.`, true);
                if (submitBtn) submitBtn.disabled = false;
                if (livePulse) livePulse.innerText = 'READY';
                focusBarcodeInput();
                return;
            }

            activeItem = lookupData.item;
            renderItemDetails(lookupData.item, lookupData.recent_movements || []);

            // Check if we need to auto-apply stock action (in or out mode)
            if (currentMode === 'in' || currentMode === 'out') {
                const qty = parseInt(document.getElementById('actionQty')?.value, 10) || 1;
                const picName = document.getElementById('actionPic')?.value || '';
                const notes = document.getElementById('actionNotes')?.value || '';

                await executeStockAction(activeItem.id, currentMode, qty, picName, notes);
            } else {
                showFeedback(`Berhasil menemukan barang: ${activeItem.name} (${activeItem.sku}).`, false);
                addSessionLog({
                    time: new Date().toLocaleTimeString('id-ID', { hour12: false }),
                    sku: activeItem.sku,
                    name: activeItem.name,
                    action_type: 'check',
                    quantity: 0,
                    current_stock: activeItem.current_stock,
                    unit: activeItem.unit,
                    pic_name: 'Cek Stok'
                });
            }

        } catch (err) {
            showFeedback('Terjadi kendala jaringan saat memproses scan.', true);
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            if (livePulse) livePulse.innerText = 'READY';
            focusBarcodeInput();
        }
    }

    async function executeStockAction(itemId, actionType, qty, picName, notes) {
        try {
            const res = await fetch("{{ route('stin.scanner.action') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    warehouse_item_id: itemId,
                    action_type: actionType,
                    quantity: qty,
                    pic_name: picName,
                    notes: notes
                })
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                showFeedback(data.message || 'Gagal melakukan mutasi stok.', true);
                return;
            }

            // Update local display stock
            activeItem.current_stock = data.item.current_stock;
            const stockEl = document.getElementById('detailStock');
            if (stockEl) stockEl.innerText = Number(data.item.current_stock).toLocaleString('id-ID');

            showFeedback(data.message, false);
            if (data.log) {
                addSessionLog(data.log);
            }

        } catch (err) {
            showFeedback('Terjadi kesalahan pada mutasi stok.', true);
        }
    }

    function triggerDirectAction(type) {
        if (!activeItem) return;
        const qtyPrompt = prompt(`Masukkan jumlah barang ${type === 'in' ? 'MASUK' : 'KELUAR'}:`, '1');
        if (!qtyPrompt) return;
        const qty = parseInt(qtyPrompt, 10);
        if (isNaN(qty) || qty < 1) {
            alert('Jumlah tidak valid.');
            return;
        }

        const pic = document.getElementById('actionPic')?.value || '';
        const notes = document.getElementById('actionNotes')?.value || '';
        executeStockAction(activeItem.id, type, qty, pic, notes);
    }

    function renderItemDetails(item, movements) {
        document.getElementById('emptyResultPlaceholder')?.classList.add('hidden');
        document.getElementById('activeItemDetails')?.classList.remove('hidden');

        document.getElementById('detailCategory').innerText = item.category || '-';
        document.getElementById('detailName').innerText = item.name;
        document.getElementById('detailSku').innerText = item.sku;
        document.getElementById('detailStock').innerText = Number(item.current_stock).toLocaleString('id-ID');
        document.getElementById('detailUnit').innerText = item.unit || 'pcs';
        document.getElementById('detailPrice').innerText = item.formatted_price;

        document.getElementById('detailPrintLink').href = item.print_sku_url;
        document.getElementById('detailMutationsLink').href = item.mutations_url;
        document.getElementById('detailEditLink').href = item.edit_url;

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
                        <td class="px-4 py-2 text-[11px] text-slate-600">${m.pic_name}</td>
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
            container.innerHTML = `<div id="emptySessionLogPlaceholder" class="py-12 text-center text-xs text-slate-400">Belum ada aktivitas scan pada sesi ini.</div>`;
            return;
        }

        container.innerHTML = sessionLogs.map(log => {
            let actionBadge = '';
            if (log.action_type === 'in') {
                actionBadge = `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-900 text-white">+${log.quantity} ${log.unit} Masuk</span>`;
            } else if (log.action_type === 'out') {
                actionBadge = `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold border border-slate-300 bg-white text-slate-900">-${log.quantity} ${log.unit} Keluar</span>`;
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
                    <div class="flex items-center justify-between pt-1">
                        ${actionBadge}
                        <span class="text-[10px] text-slate-500 font-mono">Sisa: ${log.current_stock}</span>
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
        focusBarcodeInput();
    });
</script>
@endsection
