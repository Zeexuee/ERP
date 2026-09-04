<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialReceipt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    /**
     * Tampilkan daftar barang gudang (bahan baku) dan ringkasan stok.
     */
    public function index(Request $request): View
    {
        $query = Material::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('storage_location', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $materials = $query->orderBy('name')->paginate(15)->withQueryString();

        $categories = Material::select('category')->distinct()->pluck('category');

        $totalMaterials = Material::count();
        $totalStockValue = Material::all()->sum(fn ($m) => (float) $m->stock_quantity * (float) $m->unit_cost);
        $lowStockCount = Material::whereColumn('stock_quantity', '<=', 'minimum_stock')->count();

        $recentReceipts = MaterialReceipt::with('material')
            ->latest('received_date')
            ->latest('id')
            ->take(6)
            ->get();

        return view('production.materials.index', compact(
            'materials',
            'categories',
            'totalMaterials',
            'totalStockValue',
            'lowStockCount',
            'recentReceipts'
        ));
    }

    /**
     * Formulir pencatatan barang masuk (Stock In).
     */
    public function createReceipt(): View
    {
        $materials = Material::orderBy('name')->get();

        $latestReceipt = MaterialReceipt::latest('id')->first();
        $nextNumber = 'IN-'.date('Y').'-'.str_pad(($latestReceipt ? $latestReceipt->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        return view('production.materials.create-receipt', compact('materials', 'nextNumber'));
    }

    /**
     * Simpan data barang masuk & update stok bahan gudang secara otomatis.
     */
    public function storeReceipt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'material_id' => ['required', 'exists:materials,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'source_or_supplier' => ['required', 'string', 'max:255'],
            'received_date' => ['required', 'date'],
            'received_by' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $material = Material::findOrFail($validated['material_id']);

        $latestReceipt = MaterialReceipt::latest('id')->first();
        $receiptNumber = 'IN-'.date('Y').'-'.str_pad(($latestReceipt ? $latestReceipt->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        MaterialReceipt::create([
            'receipt_number' => $receiptNumber,
            'material_id' => $material->id,
            'quantity' => $validated['quantity'],
            'unit' => $material->unit,
            'unit_cost' => $validated['unit_cost'] ?? $material->unit_cost,
            'source_or_supplier' => $validated['source_or_supplier'],
            'received_date' => $validated['received_date'],
            'received_by' => $validated['received_by'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Tambah stok bahan di gudang
        $material->increment('stock_quantity', $validated['quantity']);

        if (! empty($validated['unit_cost']) && $validated['unit_cost'] > 0) {
            $material->update(['unit_cost' => $validated['unit_cost']]);
        }

        return redirect()
            ->route('production.materials.index')
            ->with('success', "Barang masuk #{$receiptNumber} ({$validated['quantity']} {$material->unit} {$material->name}) berhasil dicatat dan stok gudang telah diperbarui.");
    }

    /**
     * Tambah jenis barang / bahan baku baru ke katalog gudang.
     */
    public function storeMaterial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:materials,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'storage_location' => ['nullable', 'string', 'max:255'],
        ]);

        $material = Material::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'category' => $validated['category'],
            'unit' => strtolower($validated['unit']),
            'stock_quantity' => $validated['stock_quantity'],
            'minimum_stock' => $validated['minimum_stock'] ?? 0,
            'unit_cost' => $validated['unit_cost'] ?? 0,
            'storage_location' => $validated['storage_location'] ?? null,
        ]);

        return back()->with('success', "Bahan baku [{$material->code}] {$material->name} berhasil ditambahkan ke daftar barang gudang.");
    }
}
