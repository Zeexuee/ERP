<?php

namespace App\Http\Controllers;

use App\Models\WarehouseItem;
use App\Models\WarehouseStockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StinScannerController extends Controller
{
    /**
     * Tampilkan halaman sistem scanner barcode / QR STIN.
     */
    public function index(): View
    {
        $recentMovements = WarehouseStockMovement::with('item')
            ->latest('id')
            ->take(10)
            ->get();

        return view('stin.scanner.index', compact('recentMovements'));
    }

    /**
     * Lookup / pencarian data barang berdasarkan kode SKU, Barcode, atau QR Code.
     */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $code = trim($request->string('code')->value());

        /** @var WarehouseItem|null $item */
        $item = WarehouseItem::where('sku', $code)
            ->orWhere('barcode', $code)
            ->orWhere('qr_code', $code)
            ->first();

        // Fallback pencarian case-insensitive jika belum ketemu
        if (! $item) {
            $item = WarehouseItem::whereRaw('LOWER(sku) = ?', [strtolower($code)])
                ->orWhereRaw('LOWER(barcode) = ?', [strtolower($code)])
                ->first();
        }

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => "Barang dengan kode '{$code}' tidak ditemukan.",
            ], 404);
        }

        $movements = $item->stockMovements()
            ->latest('id')
            ->take(5)
            ->get()
            ->map(function ($m) {
                return [
                    'date' => ($m->movement_date ?? $m->created_at)?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') ?? '-',
                    'type' => $m->type,
                    'type_label' => match ($m->type) {
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        'transfer' => 'Transfer',
                        'adjustment' => 'Penyesuaian',
                        default => ucfirst($m->type)
                    },
                    'quantity' => $m->quantity,
                    'stock_before' => $m->stock_before,
                    'stock_after' => $m->stock_after,
                    'pic_name' => $m->pic_name ?? '-',
                    'notes' => $m->notes ?? '-',
                ];
            });

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'barcode' => $item->barcode,
                'category' => $item->category ?? '-',
                'current_stock' => $item->current_stock,
                'unit' => $item->unit ?? 'pcs',
                'unit_price' => (float) $item->unit_price,
                'formatted_price' => 'Rp '.number_format($item->unit_price, 0, ',', '.'),
                'edit_url' => route('stin.items.edit', $item),
                'mutations_url' => route('stin.items.mutations', $item),
                'print_sku_url' => route('stin.items.print-sku', $item),
            ],
            'recent_movements' => $movements,
        ]);
    }

    /**
     * Proses aksi cepat stok (Barang Masuk / Barang Keluar) via scanner.
     */
    public function action(Request $request): JsonResponse
    {
        $request->validate([
            'warehouse_item_id' => ['required', 'exists:warehouse_items,id'],
            'action_type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'pic_name' => ['nullable', 'string', 'max:100'],
        ]);

        /** @var WarehouseItem $item */
        $item = WarehouseItem::findOrFail($request->input('warehouse_item_id'));
        $qty = (int) $request->input('quantity');
        $actionType = $request->input('action_type');
        $notes = $request->input('notes') ?: ($actionType === 'in' ? 'Stok masuk via scanner' : 'Stok keluar via scanner');
        $picName = $request->filled('pic_name')
            ? $request->string('pic_name')->trim()->value()
            : (auth()->user()?->name ?? 'Petugas STIN');

        if ($actionType === 'out') {
            if ($qty > $item->current_stock) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok tidak mencukupi. Stok saat ini: {$item->current_stock} {$item->unit}, diminta: {$qty} {$item->unit}.",
                ], 422);
            }

            $item->removeStock(
                quantity: $qty,
                notes: $notes,
                picName: $picName
            );

            $message = "Stok keluar berhasil: -{$qty} {$item->unit} ({$item->name}).";
        } else {
            $item->addStock(
                quantity: $qty,
                notes: $notes,
                picName: $picName
            );

            $message = "Stok masuk berhasil: +{$qty} {$item->unit} ({$item->name}).";
        }

        $item->refresh();

        return response()->json([
            'success' => true,
            'message' => $message,
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'current_stock' => $item->current_stock,
                'unit' => $item->unit ?? 'pcs',
            ],
            'log' => [
                'time' => now()->timezone('Asia/Jakarta')->format('H:i:s'),
                'sku' => $item->sku,
                'name' => $item->name,
                'action_type' => $actionType,
                'quantity' => $qty,
                'current_stock' => $item->current_stock,
                'unit' => $item->unit ?? 'pcs',
                'pic_name' => $picName,
            ],
        ]);
    }
}
