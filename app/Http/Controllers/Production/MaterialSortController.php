<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialSortBatch;
use App\Services\Production\MaterialSortService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialSortController extends Controller
{
    /**
     * Tampilkan riwayat proses sortir kayu mandiri.
     */
    public function index(Request $request): View
    {
        $query = MaterialSortBatch::with(['sourceMaterial', 'items.targetMaterial'])
            ->latest('sort_date')
            ->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sort_code', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhereHas('sourceMaterial', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        $sortBatches = $query->paginate(15)->withQueryString();

        $totalBatches = MaterialSortBatch::count();
        $totalRawProcessed = MaterialSortBatch::sum('initial_weight');
        $totalDriedProduced = MaterialSortBatch::sum('dried_weight');
        $totalLoss = MaterialSortBatch::sum('drying_loss_weight');

        return view('production.sorts.index', compact(
            'sortBatches',
            'totalBatches',
            'totalRawProcessed',
            'totalDriedProduced',
            'totalLoss'
        ));
    }

    /**
     * Formulir pencatatan proses sortir mandiri baru.
     */
    public function create(): View
    {
        // Ambil semua bahan baku untuk pilihan bahan mentah dan bahan target hasil sortir
        $materials = Material::orderBy('name')->get();

        return view('production.sorts.create', compact('materials'));
    }

    /**
     * Simpan inisiasi proses sortir kayu mandiri (hanya mencatat bahan mentah asal, berat awal, & alokasi sortir).
     */
    public function store(Request $request, MaterialSortService $service): RedirectResponse
    {
        // Mendukung mode lengkap jika items disertakan (backward-compatibility)
        if ($request->has('items') && is_array($request->input('items'))) {
            $validated = $request->validate([
                'source_material_id' => ['required', 'exists:materials,id'],
                'initial_weight' => ['required', 'numeric', 'min:0.01'],
                'dried_weight' => ['required', 'numeric', 'min:0.01'],
                'sort_date' => ['required', 'date'],
                'pic_name' => ['required', 'string', 'max:100'],
                'notes' => ['nullable', 'string', 'max:1000'],
                'signature_data' => ['nullable', 'string'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.target_material_id' => ['required', 'exists:materials,id'],
                'items.*.result_weight' => ['required', 'numeric', 'min:0.01'],
                'items.*.notes' => ['nullable', 'string', 'max:255'],
            ], [
                'source_material_id.required' => 'Pilih bahan baku mentah yang akan disortir.',
                'initial_weight.required' => 'Masukkan berat awal bahan baku.',
                'dried_weight.required' => 'Masukkan berat hasil sortir.',
                'items.required' => 'Masukkan minimal satu jenis bahan hasil sortir kayu tembak.',
            ]);

            try {
                $batch = $service->createSortBatch($validated);

                return redirect()
                    ->route('production.sorts.show', $batch)
                    ->with('success', "Proses sortir {$batch->sort_code} berhasil disimpan dan stok bahan tembak telah diperbarui.");
            } catch (\InvalidArgumentException $e) {
                return back()
                    ->withInput()
                    ->withErrors(['sort' => $e->getMessage()]);
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Gagal memproses sortir bahan: '.$e->getMessage());
            }
        }

        // Mode Inisiasi Murni (Tanpa pembagian hasil sortir)
        $validated = $request->validate([
            'source_material_id' => ['required', 'exists:materials,id'],
            'initial_weight' => ['required', 'numeric', 'min:0.01'],
            'sort_date' => ['required', 'date'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'signature_data' => ['nullable', 'string'],
        ], [
            'source_material_id.required' => 'Pilih bahan baku mentah yang akan diinisiasi untuk sortir.',
            'initial_weight.required' => 'Masukkan berat awal bahan mentah (kg).',
            'initial_weight.min' => 'Berat bahan awal harus lebih dari 0 kg.',
            'sort_date.required' => 'Tentukan tanggal inisiasi proses sortir.',
            'pic_name.required' => 'Isi nama petugas / penanggung jawab (PIC).',
        ]);

        try {
            $batch = $service->initiateSortBatch($validated);

            return redirect()
                ->route('production.sorts.show', $batch)
                ->with('success', "Inisiasi proses sortir #{$batch->sort_code} berhasil dicatat. Bahan baku mentah sedang dalam proses sortir.");
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['source_material_id' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mencatat inisiasi sortir: '.$e->getMessage());
        }
    }

    /**
     * Tampilkan detail batch sortir dan formulir laporan jika belum selesai.
     */
    public function show(MaterialSortBatch $sortBatch): View
    {
        $sortBatch->load(['sourceMaterial', 'items.targetMaterial']);
        $sourceUnit = $sortBatch->sourceMaterial?->unit;

        $materialsQuery = Material::orderBy('category')->orderBy('name');
        if ($sourceUnit) {
            $materialsQuery->whereRaw('LOWER(unit) = ?', [strtolower(trim($sourceUnit))]);
        }
        $materials = $materialsQuery->get();
        $categories = Material::select('category')->distinct()->whereNotNull('category')->pluck('category');

        return view('production.sorts.show', compact('sortBatch', 'materials', 'categories'));
    }

    /**
     * Simpan Laporan Hasil Sortir (Report Sorting): Hasil sortir & Pembagian bahan kayu tembak.
     */
    public function storeReport(Request $request, MaterialSortBatch $sortBatch, MaterialSortService $service): RedirectResponse
    {
        $validated = $request->validate([
            'dried_weight' => ['required', 'numeric', 'min:0.01'],
            'report_date' => ['required', 'date'],
            'report_pic_name' => ['required', 'string', 'max:100'],
            'report_signature_data' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.target_material_id' => ['required', 'exists:materials,id'],
            'items.*.result_weight' => ['required', 'numeric', 'min:0.01'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'dried_weight.required' => 'Masukkan berat hasil penimbangan akhir sortir (kg).',
            'report_date.required' => 'Pilih tanggal pelaporan hasil sortir.',
            'report_pic_name.required' => 'Masukkan nama petugas pelapor hasil sortir.',
            'items.required' => 'Masukkan minimal satu jenis bahan kayu tembak hasil sortir.',
            'items.*.target_material_id.required' => 'Pilih jenis bahan hasil sortir.',
            'items.*.result_weight.required' => 'Masukkan kuantitas hasil sortir (kg).',
        ]);

        try {
            $service->submitSortReport($sortBatch, $validated);

            return redirect()
                ->route('production.sorts.show', $sortBatch)
                ->with('success', "Laporan hasil sortir #{$sortBatch->sort_code} berhasil disimpan dan bahan siap tembak telah masuk ke stok gudang.");
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['report' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses laporan sortir: '.$e->getMessage());
        }
    }
}
