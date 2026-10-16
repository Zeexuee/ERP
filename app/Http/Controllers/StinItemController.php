<?php

namespace App\Http\Controllers;

use App\Models\WarehouseBranch;
use App\Models\WarehouseItem;
use App\Services\BarcodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StinItemController extends Controller
{
    /**
     * Tampilkan daftar data gudang (barang).
     */
    public function index(Request $request): View
    {
        $query = WarehouseItem::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('stin.items.index', compact('items'));
    }

    /**
     * Form input data barang (tambah stok barang ada atau barang baru).
     */
    public function create(): View
    {
        $existingItems = WarehouseItem::where('is_active', true)
            ->orderBy('name')
            ->get();

        $existingCategories = WarehouseItem::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->values();

        return view('stin.items.create', compact('existingItems', 'existingCategories'));
    }

    /**
     * Simpan data input barang.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'entry_type' => ['required', 'in:existing,new'],
            'warehouse_item_id' => ['required_if:entry_type,existing', 'nullable', 'exists:warehouse_items,id'],
            'quantity_add' => ['required_if:entry_type,existing', 'nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'name' => ['required_if:entry_type,new', 'nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:30'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:warehouse_items,sku'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'signature_data' => ['nullable', 'string'],
        ]);

        // Simpan tanda tangan jika ada
        $signaturePath = null;
        if (! empty($request->input('signature_data')) && str_contains($request->input('signature_data'), 'base64,')) {
            $base64Data = explode('base64,', $request->input('signature_data'))[1] ?? null;
            if ($base64Data) {
                $fileName = 'signatures/sig_wh_'.uniqid().'_'.time().'.png';
                Storage::disk('public')->put($fileName, base64_decode($base64Data));
                $signaturePath = $fileName;
            }
        }

        $picName = $request->filled('pic_name')
            ? $request->string('pic_name')->trim()->value()
            : (auth()->user()?->name ?? 'Petugas STIN');

        if ($request->input('entry_type') === 'existing') {
            /** @var WarehouseItem $item */
            $item = WarehouseItem::findOrFail($request->input('warehouse_item_id'));
            $qty = (int) $request->input('quantity_add');
            $notes = $request->input('notes');

            $item->addStock(
                quantity: $qty,
                notes: $notes,
                picName: $picName,
                signaturePath: $signaturePath
            );

            return redirect()->route('stin.items.index')
                ->with('success', "Stok barang '{$item->name}' berhasil ditambahkan sebanyak {$qty} {$item->unit}.");
        }

        // Buat Barang Baru
        $branch = WarehouseBranch::first() ?? WarehouseBranch::create([
            'code' => 'STIN',
            'name' => 'Gudang STIN',
            'is_active' => true,
        ]);

        $initialStock = (int) $request->input('initial_stock', 0);
        $unit = $request->input('unit', 'pcs') ?: 'pcs';
        $sku = $request->filled('sku') ? $request->string('sku')->trim()->value() : null;

        $item = WarehouseItem::create([
            'warehouse_branch_id' => $branch->id,
            'name' => $request->string('name')->trim()->value(),
            'category' => $request->filled('category') ? $request->string('category')->trim()->value() : null,
            'current_stock' => 0,
            'unit' => $unit,
            'unit_price' => (float) ($request->input('unit_price', 0) ?: 0),
            'sku' => $sku,
            'pic_name' => $picName,
            'signature_path' => $signaturePath,
            'is_active' => true,
        ]);

        if ($initialStock > 0) {
            $notes = $request->input('notes') ?: 'Stok awal barang baru';
            $item->addStock(
                quantity: $initialStock,
                notes: $notes,
                picName: $picName,
                signaturePath: $signaturePath
            );
        }

        return redirect()->route('stin.items.index')
            ->with('success', "Barang baru '{$item->name}' (SKU: {$item->sku}) berhasil ditambahkan.");
    }

    /**
     * Form edit data barang.
     */
    public function edit(WarehouseItem $item): View
    {
        $existingCategories = WarehouseItem::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->values();

        return view('stin.items.edit', compact('item', 'existingCategories'));
    }

    /**
     * Perbarui data barang.
     */
    public function update(Request $request, WarehouseItem $item): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:30'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:warehouse_items,sku,'.$item->id],
        ]);

        $item->update([
            'name' => $request->string('name')->trim()->value(),
            'category' => $request->filled('category') ? $request->string('category')->trim()->value() : null,
            'unit' => $request->input('unit', 'pcs') ?: 'pcs',
            'unit_price' => (float) ($request->input('unit_price', 0) ?: 0),
            'sku' => $request->filled('sku') ? $request->string('sku')->trim()->value() : $item->sku,
        ]);

        return redirect()->route('stin.items.index')
            ->with('success', "Data barang '{$item->name}' berhasil diperbarui.");
    }

    /**
     * Tampilkan riwayat mutasi stok untuk barang tertentu.
     */
    public function mutations(WarehouseItem $item): View
    {
        $movements = $item->stockMovements()
            ->latest('movement_date')
            ->latest('id')
            ->paginate(15);

        return view('stin.items.mutations', compact('item', 'movements'));
    }

    /**
     * Halaman cetak barcode 1D dan 2D (QR) untuk SKU barang.
     */
    public function printSku(Request $request, WarehouseItem $item, BarcodeService $barcodeService): View
    {
        if (empty($item->sku)) {
            $item->sku = WarehouseItem::generateSKU();
            $item->save();
        }

        $copies = max(1, min(100, (int) $request->input('copies', 1)));
        $layout = $request->input('layout', 'both');
        if (! in_array($layout, ['both', '1d', '2d'], true)) {
            $layout = 'both';
        }

        $size = $request->input('size', 'standard');
        if (! in_array($size, ['standard', 'compact', 'large'], true)) {
            $size = 'standard';
        }

        $barcode1d = $barcodeService->generateItem1DSVG($item, 2, 45);
        $barcode2d = $barcodeService->generateItem2DSVG($item, 140);

        return view('stin.items.print-sku', compact(
            'item',
            'barcode1d',
            'barcode2d',
            'copies',
            'layout',
            'size'
        ));
    }
}
