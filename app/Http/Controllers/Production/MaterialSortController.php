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

        $ongoingBatches = (clone $query)->where('status', 'in_progress')->paginate(10, ['*'], 'ongoing_page')->withQueryString();
        $completedBatches = (clone $query)->where('status', 'completed')->paginate(15, ['*'], 'completed_page')->withQueryString();

        $totalBatches = MaterialSortBatch::count();
        $totalRawProcessed = MaterialSortBatch::sum('initial_weight');
        $totalDriedProduced = MaterialSortBatch::sum('dried_weight');
        $totalLoss = MaterialSortBatch::sum('drying_loss_weight');

        return view('production.sorts.index', compact(
            'ongoingBatches',
            'completedBatches',
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
        // Hanya tampilkan bahan baku gudang berkategori KCB dengan stok > 0 yang bebas proses aktif
        $materials = Material::availableForSort()->orderBy('name')->get();

        return view('production.sorts.create', compact('materials'));
    }

    /**
     * Simpan inisiasi proses sortir kayu mandiri (hanya mencatat bahan mentah asal, berat awal, & alokasi sortir).
     */
    public function store(Request $request, MaterialSortService $service): RedirectResponse
    {
        $sourceMaterialRule = [
            'required',
            'exists:materials,id',
            function ($attribute, $value, $fail) {
                $material = Material::find($value);
                if (! $material) {
                    return;
                }
                if ($material->category !== 'KCB') {
                    $fail('Hanya bahan baku berkategori KCB yang dapat diinisiasi untuk proses sortir.');
                } elseif ($material->isInActiveProcess()) {
                    $processName = $material->getActiveProcessName() ?? 'lain';
                    $fail("Bahan baku ini sedang dalam proses {$processName} dan belum selesai.");
                }
            },
        ];

        // Mendukung mode lengkap jika items disertakan (backward-compatibility)
        if ($request->has('items') && is_array($request->input('items'))) {
            $validated = $request->validate([
                'source_material_id' => $sourceMaterialRule,
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
            'source_material_id' => $sourceMaterialRule,
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

        $targetCategories = ['Bahan Tembak', 'Bahan Potong Ukir'];

        $materialsQuery = Material::where(function ($q) {
            $q->whereIn('category', ['Bahan Tembak', 'Bahan Potong Ukir', 'Bahan Ukir Potong'])
                ->orWhereRaw('LOWER(TRIM(category)) IN (?, ?, ?)', ['bahan tembak', 'bahan potong ukir', 'bahan ukir potong']);
        })
            ->whereKeyNot($sortBatch->source_material_id)
            ->orderBy('category')
            ->orderBy('name');

        if ($sourceUnit) {
            $materialsQuery->whereRaw('LOWER(unit) = ?', [strtolower(trim($sourceUnit))]);
        }
        $materials = $materialsQuery->get();
        $categories = $targetCategories;

        return view('production.sorts.show', compact('sortBatch', 'materials', 'categories'));
    }

    /**
     * Simpan Laporan Hasil Sortir (Report Sorting): Hasil sortir & Pembagian bahan kayu tembak.
     */
    public function storeReport(Request $request, MaterialSortBatch $sortBatch, MaterialSortService $service): RedirectResponse
    {
        if ($request->input('action') === 'draft') {
            try {
                $service->saveDraftSortReport($sortBatch, $request->all());

                return redirect()
                    ->route('production.sorts.show', $sortBatch)
                    ->with('success', "Progres laporan sortir #{$sortBatch->sort_code} berhasil disimpan sementara. Anda dapat melanjutkannya kapan saja.");
            } catch (\InvalidArgumentException $e) {
                return back()
                    ->withInput()
                    ->withErrors(['report' => $e->getMessage()]);
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Gagal menyimpan draf sortir: '.$e->getMessage());
            }
        }

        $validated = $request->validate([
            'report_date' => ['required', 'date'],
            'report_pic_name' => ['required', 'string', 'max:100'],
            'returned_weight' => ['nullable', 'numeric', 'min:0'],
            'report_signature_data' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.target_material_id' => [
                'required',
                'exists:materials,id',
                function ($attribute, $value, $fail) use ($sortBatch) {
                    if ((int) $value === (int) $sortBatch->source_material_id) {
                        $fail('Bahan hasil sortir tidak boleh sama dengan bahan baku mentah asal yang sedang disortir.');
                    }
                    $target = Material::find($value);
                    if ($target) {
                        $catLower = strtolower(trim((string) $target->category));
                        if (! in_array($catLower, ['bahan tembak', 'bahan potong ukir', 'bahan ukir potong'], true)) {
                            $fail("Bahan hasil sortir [{$target->name}] harus berkategori Bahan Tembak atau Bahan Potong Ukir (kategori saat ini: {$target->category}).");
                        }
                    }
                },
            ],
            'items.*.result_weight' => ['required', 'numeric', 'min:0.01'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'report_date.required' => 'Pilih tanggal pelaporan hasil sortir.',
            'report_pic_name.required' => 'Masukkan nama petugas pelapor hasil sortir.',
            'returned_weight.min' => 'Berat bahan yang dikembalikan tidak boleh bernilai negatif.',
            'items.required' => 'Masukkan minimal satu jenis bahan hasil sortir.',
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
