<?php

namespace App\Http\Controllers\Production;

use App\Enums\ProductionRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchMaterial;
use App\Models\ProductionDailyLog;
use App\Models\ProductionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductionBatchController extends Controller
{
    /**
     * Tampilkan daftar proses produksi (Work Orders / Batches).
     */
    public function index(Request $request): View
    {
        $query = ProductionBatch::with(['product', 'productionRequest.salesOrder.customer', 'batchMaterials.material'])
            ->latest('start_date')
            ->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('stage', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $batches = $query->paginate(15)->withQueryString();

        $totalActive = ProductionBatch::where('status', 'in_progress')->count();
        $totalCompleted = ProductionBatch::where('status', 'completed')->count();
        $totalTargetQty = ProductionBatch::where('status', 'in_progress')->sum('target_quantity');

        return view('production.batches.index', compact(
            'batches',
            'totalActive',
            'totalCompleted',
            'totalTargetQty'
        ));
    }

    /**
     * Formulir pembuatan batch produksi baru dengan input alokasi bahan (BOM).
     */
    public function create(): View
    {
        $products = Product::orderBy('name')->get();

        $pendingRequests = ProductionRequest::where('status', '!=', ProductionRequestStatus::FINISHED)
            ->with(['salesOrder.customer', 'salesOrder.items.product'])
            ->get();

        $materials = Material::orderBy('category')->orderBy('name')->get();

        $latestBatch = ProductionBatch::latest('id')->first();
        $nextBatchNumber = 'BATCH-'.date('Y').'-'.str_pad(($latestBatch ? $latestBatch->id + 1 : 1), 3, '0', STR_PAD_LEFT);

        return view('production.batches.create', compact(
            'products',
            'pendingRequests',
            'materials',
            'nextBatchNumber'
        ));
    }

    /**
     * Simpan proses produksi baru, alokasikan bahan, dan kurangi stok bahan baku.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'production_request_id' => ['nullable', 'exists:production_requests,id'],
            'target_quantity' => ['required', 'numeric', 'min:0.01'],
            'start_date' => ['required', 'date'],
            'target_completion_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'pic_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'materials' => ['required', 'array', 'min:1'],
            'materials.*.material_id' => ['required', 'exists:materials,id'],
            'materials.*.quantity_used' => ['required', 'numeric', 'min:0.01'],
        ], [
            'materials.required' => 'Setidaknya masukkan minimal satu bahan baku yang digunakan untuk produksi.',
            'materials.min' => 'Setidaknya masukkan minimal satu bahan baku yang digunakan untuk produksi.',
            'materials.*.material_id.required' => 'Bahan baku wajib dipilih.',
            'materials.*.quantity_used.required' => 'Jumlah penggunaan bahan wajib diisi.',
            'materials.*.quantity_used.min' => 'Jumlah penggunaan bahan minimal 0.01.',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Verifikasi ketersediaan stok setiap bahan
        foreach ($validated['materials'] as $item) {
            $material = Material::find($item['material_id']);
            if (! $material || $material->stock_quantity < $item['quantity_used']) {
                $available = $material ? "{$material->stock_quantity} {$material->unit}" : '0';

                return back()
                    ->withInput()
                    ->withErrors([
                        'materials' => "Stok bahan '{$material?->name}' tidak mencukupi. Tersedia: {$available}, diminta: {$item['quantity_used']} {$material?->unit}.",
                    ]);
            }
        }

        DB::beginTransaction();
        try {
            $latestBatch = ProductionBatch::latest('id')->first();
            $batchNumber = 'BATCH-'.date('Y').'-'.str_pad(($latestBatch ? $latestBatch->id + 1 : 1), 3, '0', STR_PAD_LEFT);

            $batch = ProductionBatch::create([
                'batch_number' => $batchNumber,
                'product_id' => $product->id,
                'production_request_id' => $validated['production_request_id'] ?? null,
                'target_quantity' => $validated['target_quantity'],
                'actual_quantity' => null,
                'unit' => $product->unit ?? 'kg',
                'status' => 'in_progress',
                'stage' => '1. Persiapan Bahan & Sortir Kayu',
                'start_date' => $validated['start_date'],
                'target_completion_date' => $validated['target_completion_date'] ?? null,
                'pic_name' => $validated['pic_name'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Alokasikan bahan & potong stok bahan baku di gudang
            foreach ($validated['materials'] as $matItem) {
                $mat = Material::findOrFail($matItem['material_id']);

                ProductionBatchMaterial::create([
                    'production_batch_id' => $batch->id,
                    'material_id' => $mat->id,
                    'quantity_used' => $matItem['quantity_used'],
                    'unit' => $mat->unit,
                ]);

                $mat->decrement('stock_quantity', $matItem['quantity_used']);
            }

            // Catat log harian awal pengerjaan (Hari ke-1)
            ProductionDailyLog::create([
                'production_batch_id' => $batch->id,
                'log_date' => $validated['start_date'],
                'stage' => '1. Persiapan Bahan & Sortir Kayu',
                'progress_percentage' => 15,
                'pic_name' => $validated['pic_name'],
                'notes' => "Proses produksi batch #{$batch->batch_number} resmi dimulai. Penimbangan dan alokasi bahan baku selesai dipersiapkan.",
            ]);

            // Jika terhubung ke antrean Sales, ubah status antrean menjadi In Production
            if (! empty($validated['production_request_id'])) {
                $pr = ProductionRequest::find($validated['production_request_id']);
                if ($pr && $pr->status !== ProductionRequestStatus::FINISHED) {
                    $pr->update(['status' => ProductionRequestStatus::IN_PRODUCTION]);
                }
            }

            DB::commit();

            return redirect()
                ->route('production.batches.show', $batch)
                ->with('success', "Proses produksi #{$batch->batch_number} ({$batch->target_quantity} {$batch->unit} {$product->name}) berhasil dibuat dan stok bahan telah dialokasikan.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Gagal memproses pembuatan produksi: '.$e->getMessage());
        }
    }

    /**
     * Tampilkan detail proses produksi, alokasi bahan, dan riwayat laporan harian.
     */
    public function show(ProductionBatch $batch): View
    {
        $batch->load([
            'product.branches',
            'productionRequest.salesOrder.customer',
            'batchMaterials.material',
            'dailyLogs',
        ]);

        $branches = ProductBranch::select('branch_name', 'branch_code')->distinct()->get();

        return view('production.batches.show', compact('batch', 'branches'));
    }

    /**
     * Tambah catatan laporan proses harian (Daily Production Log).
     */
    public function storeDailyLog(Request $request, ProductionBatch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'log_date' => ['required', 'date'],
            'stage' => ['required', 'string', 'max:100'],
            'progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'pic_name' => ['required', 'string', 'max:255'],
            'notes' => ['required', 'string', 'max:1000'],
        ]);

        ProductionDailyLog::create([
            'production_batch_id' => $batch->id,
            'log_date' => $validated['log_date'],
            'stage' => $validated['stage'],
            'progress_percentage' => $validated['progress_percentage'],
            'pic_name' => $validated['pic_name'],
            'notes' => $validated['notes'],
        ]);

        // Perbarui tahapan saat ini pada batch
        $batch->update([
            'stage' => $validated['stage'],
        ]);

        return back()->with('success', 'Laporan harian tanggal '.Carbon::parse($validated['log_date'])->format('d/m/Y').' berhasil ditambahkan.');
    }

    /**
     * Tandai proses produksi selesai & masukkan kuantitas aktual ke stok produk jadi.
     */
    public function complete(Request $request, ProductionBatch $batch): RedirectResponse
    {
        if ($batch->status === 'completed') {
            return back()->with('error', 'Proses produksi ini sudah diselesaikan sebelumnya.');
        }

        $validated = $request->validate([
            'actual_quantity' => ['required', 'numeric', 'min:0.01'],
            'completed_date' => ['required', 'date'],
            'branch_code' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        try {
            $batch->update([
                'status' => 'completed',
                'stage' => '5. Selesai & Masuk Stok Barang Jadi',
                'actual_quantity' => $validated['actual_quantity'],
                'completed_date' => $validated['completed_date'],
            ]);

            // Tambahkan ke stok katalog Produk Jadi
            $product = $batch->product;
            $product->increment('stock_quantity', (int) $validated['actual_quantity']);

            // Tambahkan atau perbarui ke cabang/gudang pabrik
            $branchCode = $validated['branch_code'] ?? 'PLANT-STL';
            $branchName = $branchCode === 'PLANT-STL' ? 'Pabrik Pengolahan Sentul' : 'Gudang Utama Jakarta';

            $branch = ProductBranch::where('product_id', $product->id)
                ->where('branch_code', $branchCode)
                ->first();

            if ($branch) {
                $branch->increment('quantity', (int) $validated['actual_quantity']);
            } else {
                ProductBranch::create([
                    'product_id' => $product->id,
                    'branch_name' => $branchName,
                    'branch_code' => $branchCode,
                    'quantity' => (int) $validated['actual_quantity'],
                    'specification_notes' => "Hasil proses produksi batch #{$batch->batch_number}.",
                ]);
            }

            // Tambah log penyelesaian
            ProductionDailyLog::create([
                'production_batch_id' => $batch->id,
                'log_date' => $validated['completed_date'],
                'stage' => '5. Selesai & Masuk Stok Barang Jadi',
                'progress_percentage' => 100,
                'pic_name' => auth()->user()?->name ?? 'Admin Produksi',
                'notes' => "Produksi selesai dan lolos uji Quality Control. Sebanyak {$validated['actual_quantity']} {$batch->unit} barang jadi telah dimasukkan ke stok katalog produk ({$branchCode}).".(! empty($validated['notes']) ? " Catatan: {$validated['notes']}" : ''),
            ]);

            // Jika terhubung ke antrean Sales, ubah status antrean menjadi Finished
            if ($batch->production_request_id && $batch->productionRequest) {
                $batch->productionRequest->update([
                    'status' => ProductionRequestStatus::FINISHED,
                    'completed_date' => $validated['completed_date'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('production.batches.show', $batch)
                ->with('success', "Proses produksi #{$batch->batch_number} berhasil diselesaikan. {$validated['actual_quantity']} {$batch->unit} {$product->name} telah ditambahkan ke stok barang jadi.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyelesaikan produksi: '.$e->getMessage());
        }
    }
}
