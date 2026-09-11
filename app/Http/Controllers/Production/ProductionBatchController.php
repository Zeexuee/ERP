<?php

namespace App\Http\Controllers\Production;

use App\Enums\ProductionRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Production\StoreProductionBatchRequest;
use App\Http\Requests\Production\StoreProductionDailyLogRequest;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductionBatch;
use App\Models\ProductionDailyLog;
use App\Models\ProductionRequest;
use App\Services\Production\ProductionBatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function store(StoreProductionBatchRequest $request, ProductionBatchService $batchService): RedirectResponse
    {
        try {
            $batch = $batchService->createBatch($request->validated());

            return redirect()
                ->route('production.batches.show', $batch)
                ->with('success', "Proses produksi #{$batch->batch_number} ({$batch->target_quantity} {$batch->unit} {$batch->product->name}) berhasil dibuat dan stok bahan telah dialokasikan.");
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['materials' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses pembuatan produksi: '.$e->getMessage());
        }
    }

    /**
     * Tampilkan detail proses produksi, alokasi bahan, dan riwayat laporan harian.
     */
    public function show($batch): View|RedirectResponse
    {
        $batchModel = $batch instanceof ProductionBatch ? $batch : ProductionBatch::find($batch);

        if (! $batchModel) {
            $fallback = ProductionBatch::first();
            if ($fallback) {
                return redirect()->route('production.batches.show', $fallback);
            }
            abort(404, 'Data proses produksi tidak ditemukan.');
        }

        $batchModel->load([
            'product.branches',
            'productionRequest.salesOrder.customer',
            'batchMaterials.material',
            'dailyLogs',
        ]);

        $branches = ProductBranch::select('branch_name', 'branch_code')->distinct()->get();
        $materials = Material::orderBy('category')->orderBy('name')->get();

        return view('production.batches.show', [
            'batch' => $batchModel,
            'branches' => $branches,
            'materials' => $materials,
        ]);
    }

    /**
     * Tambah catatan laporan proses harian (Daily Production Log).
     */
    public function storeDailyLog(StoreProductionDailyLogRequest $request, ProductionBatch $batch, ProductionBatchService $batchService): RedirectResponse
    {
        try {
            $files = $request->file('attachments') ?? $request->file('attachment');
            $batchService->storeDailyLog($batch, $request->validated(), $files);

            $msg = 'Laporan harian berhasil disimpan.';
            if ($request->validated()['stage'] === 'Selesai') {
                $msg = "Status produksi batch #{$batch->batch_number} berhasil diselesaikan dan stok barang jadi telah diperbarui.";
            }

            return back()->with('success', $msg);
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['materials' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan laporan harian: '.$e->getMessage());
        }
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

    /**
     * Tambah alokasi bahan baku ke komposisi BOM batch produksi.
     */
    public function addMaterial(Request $request, ProductionBatch $batch, ProductionBatchService $batchService): RedirectResponse
    {
        $validated = $request->validate([
            'material_id' => ['required', 'exists:materials,id'],
            'quantity_used' => ['required', 'numeric', 'min:0.01'],
        ], [
            'material_id.required' => 'Silakan pilih bahan baku dari gudang terlebih dahulu.',
            'material_id.exists' => 'Bahan baku yang dipilih tidak ditemukan di data gudang.',
            'quantity_used.required' => 'Kuantitas bahan yang dialokasikan wajib diisi.',
            'quantity_used.min' => 'Kuantitas bahan minimal 0.01.',
        ]);

        try {
            $batchService->addMaterialToBatch($batch, $validated);

            return back()->with('success', 'Bahan baku berhasil ditambahkan ke komposisi BOM batch ini.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menambahkan bahan ke BOM: '.$e->getMessage());
        }
    }
}
