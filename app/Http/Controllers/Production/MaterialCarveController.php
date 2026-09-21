<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialCarveBatch;
use App\Services\Production\MaterialCarveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialCarveController extends Controller
{
    /**
     * Tampilkan riwayat proses potong ukir mandiri.
     */
    public function index(Request $request): View
    {
        $query = MaterialCarveBatch::with(['sourceMaterial', 'items.targetMaterial'])
            ->latest('carve_date')
            ->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('carve_code', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhereHas('sourceMaterial', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        $ongoingBatches = (clone $query)->where('status', 'in_progress')->paginate(10, ['*'], 'ongoing_page')->withQueryString();
        $completedBatches = (clone $query)->where('status', 'completed')->paginate(15, ['*'], 'completed_page')->withQueryString();

        $totalBatches = MaterialCarveBatch::count();
        $totalRawProcessed = MaterialCarveBatch::sum('initial_weight');
        $totalDriedProduced = MaterialCarveBatch::sum('dried_weight');
        $totalLoss = MaterialCarveBatch::sum('drying_loss_weight');

        return view('production.carves.index', compact(
            'ongoingBatches',
            'completedBatches',
            'totalBatches',
            'totalRawProcessed',
            'totalDriedProduced',
            'totalLoss'
        ));
    }

    /**
     * Formulir pencatatan proses potong ukir mandiri baru.
     */
    public function create(): View
    {
        // Ambil semua bahan baku untuk pilihan bahan mentah dan bahan target hasil potong ukir
        $materials = Material::orderBy('name')->get();

        return view('production.carves.create', compact('materials'));
    }

    /**
     * Simpan inisiasi proses potong ukir kayu mandiri.
     */
    public function store(Request $request, MaterialCarveService $service): RedirectResponse
    {
        if ($request->has('items') && is_array($request->input('items'))) {
            $validated = $request->validate([
                'source_material_id' => ['required', 'exists:materials,id'],
                'initial_weight' => ['required', 'numeric', 'min:0.01'],
                'dried_weight' => ['required', 'numeric', 'min:0.01'],
                'carve_date' => ['required', 'date'],
                'pic_name' => ['required', 'string', 'max:100'],
                'notes' => ['nullable', 'string', 'max:1000'],
                'signature_data' => ['nullable', 'string'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.target_material_id' => ['required', 'exists:materials,id'],
                'items.*.result_weight' => ['required', 'numeric', 'min:0.01'],
                'items.*.notes' => ['nullable', 'string', 'max:255'],
            ], [
                'source_material_id.required' => 'Pilih bahan baku mentah yang akan dipotong ukir.',
                'initial_weight.required' => 'Masukkan berat awal bahan baku.',
                'dried_weight.required' => 'Masukkan berat hasil.',
                'items.required' => 'Masukkan minimal satu jenis bahan hasil.',
            ]);

            try {
                $batch = $service->createCarveBatch($validated);

                return redirect()
                    ->route('production.carves.show', $batch)
                    ->with('success', "Proses potong ukir {$batch->carve_code} berhasil disimpan.");
            } catch (\InvalidArgumentException $e) {
                return back()
                    ->withInput()
                    ->withErrors(['carve' => $e->getMessage()]);
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Gagal memproses potong ukir bahan: '.$e->getMessage());
            }
        }

        // Mode Inisiasi Murni (Tanpa pembagian hasil)
        $validated = $request->validate([
            'source_material_id' => ['required', 'exists:materials,id'],
            'initial_weight' => ['required', 'numeric', 'min:0.01'],
            'carve_date' => ['required', 'date'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'signature_data' => ['nullable', 'string'],
        ], [
            'source_material_id.required' => 'Pilih bahan baku mentah yang akan diinisiasi.',
            'initial_weight.required' => 'Masukkan berat awal bahan mentah (kg).',
            'initial_weight.min' => 'Berat bahan awal harus lebih dari 0 kg.',
            'carve_date.required' => 'Tentukan tanggal inisiasi proses.',
            'pic_name.required' => 'Isi nama petugas / penanggung jawab (PIC).',
        ]);

        try {
            $batch = $service->initiateCarveBatch($validated);

            return redirect()
                ->route('production.carves.show', $batch)
                ->with('success', "Inisiasi proses potong ukir #{$batch->carve_code} berhasil dicatat.");
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['source_material_id' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mencatat inisiasi: '.$e->getMessage());
        }
    }

    /**
     * Tampilkan detail batch potong ukir dan formulir laporan jika belum selesai.
     */
    public function show(MaterialCarveBatch $carveBatch): View
    {
        $carveBatch->load(['sourceMaterial', 'items.targetMaterial']);
        $sourceUnit = $carveBatch->sourceMaterial?->unit;

        $materialsQuery = Material::orderBy('category')->orderBy('name');
        if ($sourceUnit) {
            $materialsQuery->whereRaw('LOWER(unit) = ?', [strtolower(trim($sourceUnit))]);
        }
        $materials = $materialsQuery->get();
        $categories = Material::getAllCategories();

        return view('production.carves.show', compact('carveBatch', 'materials', 'categories'));
    }

    /**
     * Simpan Laporan Hasil Potong Ukir (Report Carving): Hasil & Pembagian bahan.
     */
    public function storeReport(Request $request, MaterialCarveBatch $carveBatch, MaterialCarveService $service): RedirectResponse
    {
        $validated = $request->validate([
            'report_date' => ['required', 'date'],
            'report_pic_name' => ['required', 'string', 'max:100'],
            'report_signature_data' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.target_material_id' => ['required', 'exists:materials,id'],
            'items.*.result_weight' => ['required', 'numeric', 'min:0.01'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'report_date.required' => 'Pilih tanggal pelaporan hasil potong ukir.',
            'report_pic_name.required' => 'Masukkan nama petugas pelapor hasil potong ukir.',
            'items.required' => 'Masukkan minimal satu jenis bahan hasil potong ukir.',
            'items.*.target_material_id.required' => 'Pilih jenis bahan hasil potong ukir.',
            'items.*.result_weight.required' => 'Masukkan kuantitas hasil potong ukir (kg).',
        ]);

        try {
            $service->submitCarveReport($carveBatch, $validated);

            return redirect()
                ->route('production.carves.show', $carveBatch)
                ->with('success', "Laporan hasil potong ukir #{$carveBatch->carve_code} berhasil disimpan dan bahan siap tembak telah masuk ke stok gudang.");
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['report' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses laporan potong ukir: '.$e->getMessage());
        }
    }
}
