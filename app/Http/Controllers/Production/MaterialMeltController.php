<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialMeltBatch;
use App\Services\Production\MaterialMeltService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialMeltController extends Controller
{
    /**
     * Tampilkan daftar riwayat proses pencairan getah padat.
     */
    public function index(Request $request): View
    {
        $query = MaterialMeltBatch::with(['sourceMaterial', 'targetMaterial', 'logs'])
            ->latest('melt_date')
            ->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('melt_code', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhereHas('sourceMaterial', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $ongoingBatches = (clone $query)->where('status', 'in_progress')->paginate(10, ['*'], 'ongoing_page')->withQueryString();
        $completedBatches = (clone $query)->whereIn('status', ['completed', 'monitoring'])->paginate(15, ['*'], 'completed_page')->withQueryString();

        return view('production.melts.index', compact('ongoingBatches', 'completedBatches'));
    }

    /**
     * Formulir pencatatan inisiasi pencairan baru.
     */
    public function create(): View
    {
        $materials = Material::orderBy('name')->get();

        return view('production.melts.create', compact('materials'));
    }

    /**
     * Simpan inisiasi proses pencairan baru.
     */
    public function store(Request $request, MaterialMeltService $service): RedirectResponse
    {
        $validated = $request->validate([
            'source_material_id' => ['required', 'exists:materials,id'],
            'initial_weight' => ['required', 'numeric', 'min:0.01'],
            'melt_date' => ['required', 'date'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'source_material_id.required' => 'Pilih bahan baku getah padat yang akan dicairkan.',
            'initial_weight.required' => 'Masukkan berat getah padat.',
            'pic_name.required' => 'Isi nama penanggung jawab (PIC).',
        ]);

        try {
            $batch = $service->initiateMeltBatch($validated);

            return redirect()
                ->route('production.melts.show', $batch)
                ->with('success', "Inisiasi proses pencairan #{$batch->melt_code} berhasil dicatat dan sedang dalam pemantauan.");
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['source_material_id' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mencatat inisiasi pencairan: '.$e->getMessage());
        }
    }

    /**
     * Tampilkan detail pencairan dan riwayat penimbangan (penguapan).
     */
    public function show(MaterialMeltBatch $meltBatch): View
    {
        $meltBatch->load(['sourceMaterial', 'targetMaterial', 'logs' => function ($q) {
            $q->latest('weighed_date')->latest('id');
        }]);

        return view('production.melts.show', compact('meltBatch'));
    }

    /**
     * Catat hasil timbang ulang harian (Penguapan).
     */
    public function storeLog(Request $request, MaterialMeltBatch $meltBatch, MaterialMeltService $service): RedirectResponse
    {
        $validated = $request->validate([
            'weighed_date' => ['required', 'date'],
            'new_weight' => ['required', 'numeric', 'min:0'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'weighed_date.required' => 'Pilih tanggal timbang ulang.',
            'new_weight.required' => 'Masukkan berat terbaru cairan saat ditimbang.',
            'pic_name.required' => 'Masukkan nama penanggung jawab (PIC) yang menimbang.',
        ]);

        try {
            $service->logEvaporation($meltBatch, $validated);

            return redirect()
                ->route('production.melts.show', $meltBatch)
                ->with('success', "Hasil timbang ulang berhasil dicatat. Berat saat ini: {$validated['new_weight']} kg.");
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['new_weight' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mencatat hasil timbang: '.$e->getMessage());
        }
    }

    /**
     * Menyelesaikan pemantauan pencairan dan memasukkan sisa cairan ke Gudang Utama.
     */
    public function storeReport(Request $request, MaterialMeltBatch $meltBatch, MaterialMeltService $service): RedirectResponse
    {
        $validated = $request->validate([
            'target_material_name' => ['required', 'string', 'max:255'],
            'initial_liquid_weight' => ['required', 'numeric', 'min:0.01'],
            'pic_name' => ['required', 'string', 'max:100'],
            'report_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'target_material_name.required' => 'Masukkan nama cairan hasil pencairan untuk dimasukkan ke gudang.',
            'initial_liquid_weight.required' => 'Masukkan berat cairan awal.',
        ]);

        try {
            $service->submitMeltReport($meltBatch, $validated);

            return redirect()->route('production.melts.show', $meltBatch)
                ->with('success', 'Laporan pencairan berhasil disimpan. Cairan ditambahkan ke gudang dan pemantauan dimulai.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function complete(Request $request, MaterialMeltBatch $meltBatch, MaterialMeltService $service): RedirectResponse
    {
        try {
            $service->completeMeltBatch($meltBatch, $request->all());

            return redirect()
                ->route('production.melts.index')
                ->with('success', "Proses pencairan #{$meltBatch->melt_code} berhasil diselesaikan. Cairan telah dimasukkan ke stok Gudang Utama.");
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['complete' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyelesaikan pencairan: '.$e->getMessage());
        }
    }
}
