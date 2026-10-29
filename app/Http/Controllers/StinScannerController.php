<?php

namespace App\Http\Controllers;

use App\Models\WarehouseItem;
use App\Models\WarehouseStockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
                'currently_loaned' => $item->currentlyLoanedQuantity(),
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
            'signature' => ['nullable', 'string'],
            'signature_data' => ['nullable', 'string'],
        ]);

        /** @var WarehouseItem $item */
        $item = WarehouseItem::findOrFail($request->input('warehouse_item_id'));
        $qty = (int) $request->input('quantity');
        $actionType = $request->input('action_type');
        $notes = $request->input('notes') ?: ($actionType === 'in' ? 'Stok masuk via scanner' : 'Stok keluar via scanner');
        $picName = $request->filled('pic_name')
            ? $request->string('pic_name')->trim()->value()
            : null;

        $rawSignature = $request->input('signature_data') ?: $request->input('signature');
        $signaturePath = null;
        if (! empty($rawSignature) && str_contains($rawSignature, 'base64,')) {
            $base64Data = explode('base64,', $rawSignature)[1] ?? null;
            if ($base64Data) {
                $fileName = 'signatures/sig_wh_'.uniqid().'_'.time().'.png';
                Storage::disk('public')->put($fileName, base64_decode($base64Data));
                $signaturePath = $fileName;
            }
        }

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
                picName: $picName,
                signaturePath: $signaturePath
            );

            $message = "Stok keluar berhasil: -{$qty} {$item->unit} ({$item->name}).";
        } else {
            $item->addStock(
                quantity: $qty,
                notes: $notes,
                picName: $picName,
                signaturePath: $signaturePath
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
                'pic_name' => $picName ?: '-',
            ],
        ]);
    }

    /**
     * Proses batch mutasi stok untuk daftar barang sekaligus (Daftar Antrean / Cart Out).
     */
    public function batchAction(Request $request): JsonResponse
    {
        $request->validate([
            'action_type' => ['required', 'in:in,out'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.warehouse_item_id' => ['required', 'exists:warehouse_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'signature' => ['nullable', 'string'],
            'signature_data' => ['nullable', 'string'],
        ]);

        $actionType = $request->input('action_type');
        $notes = $request->input('notes') ?: ($actionType === 'in' ? 'Stok masuk via batch scanner' : 'Stok keluar via batch scanner');
        $picName = $request->filled('pic_name')
            ? $request->string('pic_name')->trim()->value()
            : null;

        $rawSignature = $request->input('signature_data') ?: $request->input('signature');
        $signaturePath = null;
        if (! empty($rawSignature) && str_contains($rawSignature, 'base64,')) {
            $base64Data = explode('base64,', $rawSignature)[1] ?? null;
            if ($base64Data) {
                $fileName = 'signatures/sig_wh_'.uniqid().'_'.time().'.png';
                Storage::disk('public')->put($fileName, base64_decode($base64Data));
                $signaturePath = $fileName;
            }
        }

        $itemsPayload = $request->input('items');

        try {
            $processed = DB::transaction(function () use ($itemsPayload, $actionType, $notes, $picName, $signaturePath) {
                $results = [];

                foreach ($itemsPayload as $entry) {
                    /** @var WarehouseItem $item */
                    $item = WarehouseItem::lockForUpdate()->findOrFail($entry['warehouse_item_id']);
                    $qty = (int) $entry['quantity'];

                    if ($actionType === 'out') {
                        if ($qty > $item->current_stock) {
                            throw new \Exception("Stok barang '{$item->name}' tidak mencukupi. Tersedia: {$item->current_stock} {$item->unit}, diminta: {$qty} {$item->unit}.");
                        }

                        $item->removeStock(
                            quantity: $qty,
                            notes: $notes,
                            picName: $picName,
                            signaturePath: $signaturePath
                        );
                    } else {
                        $item->addStock(
                            quantity: $qty,
                            notes: $notes,
                            picName: $picName,
                            signaturePath: $signaturePath
                        );
                    }

                    $item->refresh();

                    $results[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'sku' => $item->sku,
                        'quantity' => $qty,
                        'current_stock' => $item->current_stock,
                        'unit' => $item->unit ?? 'pcs',
                    ];
                }

                return $results;
            });

            $totalQty = array_sum(array_column($processed, 'quantity'));
            $itemCount = count($processed);
            $actionLabel = $actionType === 'in' ? 'masuk' : 'keluar';

            return response()->json([
                'success' => true,
                'message' => "Berhasil memproses {$itemCount} jenis barang (total: {$totalQty} {$actionLabel}).",
                'processed_items' => $processed,
                'log' => [
                    'time' => now()->timezone('Asia/Jakarta')->format('H:i:s'),
                    'sku' => "BATCH ({$itemCount} ITEM)",
                    'name' => "Pengeluaran {$itemCount} jenis produk ({$totalQty} pcs)",
                    'action_type' => $actionType,
                    'quantity' => $totalQty,
                    'current_stock' => '-',
                    'unit' => 'pcs',
                    'pic_name' => $picName ?: '-',
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
