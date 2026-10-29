<?php

namespace App\Http\Controllers;

use App\Enums\WarehouseLoanStatus;
use App\Models\WarehouseItem;
use App\Models\WarehouseLoan;
use App\Models\WarehouseLoanItem;
use App\Models\WarehouseLoanReturn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StinLoanController extends Controller
{
    /**
     * Tampilkan daftar peminjaman barang.
     */
    public function index(Request $request): View
    {
        $query = WarehouseLoan::with(['items.item', 'user'])
            ->withCount('items');

        // Filter status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            if ($status === 'overdue') {
                $query->whereIn('status', [WarehouseLoanStatus::BORROWED, WarehouseLoanStatus::PARTIAL])
                    ->whereNotNull('expected_return_date')
                    ->whereDate('expected_return_date', '<', today());
            } elseif ($status === 'borrowed' || $status === 'active') {
                $query->whereIn('status', [WarehouseLoanStatus::BORROWED, WarehouseLoanStatus::PARTIAL]);
            } else {
                $query->where('status', $status);
            }
        }

        // Pencarian teks
        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(function ($q) use ($search) {
                $q->where('loan_number', 'like', "%{$search}%")
                    ->orWhere('borrower_name', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('items.item', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        $loans = $query->latest('id')->paginate(15)->withQueryString();

        // Rekapitulasi Statistik
        $activeLoans = WarehouseLoan::whereIn('status', [WarehouseLoanStatus::BORROWED, WarehouseLoanStatus::PARTIAL])->get();
        $totalActiveLoans = $activeLoans->count();

        // Hitung total unit yang sedang di luar
        $totalItemsLoaned = (int) WarehouseLoanItem::whereHas('loan', function ($q) {
            $q->whereIn('status', [WarehouseLoanStatus::BORROWED, WarehouseLoanStatus::PARTIAL]);
        })->selectRaw('COALESCE(SUM(quantity_loaned - quantity_returned), 0) as remaining')->value('remaining');

        $totalOverdueLoans = $activeLoans->filter(fn (WarehouseLoan $l) => $l->isOverdue())->count();
        $totalReturnedLoans = WarehouseLoan::where('status', WarehouseLoanStatus::RETURNED)->count();

        $stats = [
            'total_loans' => WarehouseLoan::count(),
            'active_loans' => $totalActiveLoans,
            'items_on_loan' => $totalItemsLoaned,
            'overdue_loans' => $totalOverdueLoans,
            'returned_loans' => $totalReturnedLoans,
        ];

        return view('stin.loans.index', compact('loans', 'stats'));
    }

    /**
     * Form pencatatan peminjaman baru.
     */
    public function create(): View
    {
        $availableItems = WarehouseItem::where('is_active', true)
            ->where('current_stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('stin.loans.create', compact('availableItems'));
    }

    /**
     * Simpan transaksi peminjaman baru & kurangi stok fisik.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'borrower_name' => ['required', 'string', 'max:255'],
            'borrower_contact' => ['nullable', 'string', 'max:50'],
            'purpose' => ['required', 'string', 'max:255'],
            'loan_date' => ['required', 'date'],
            'expected_return_date' => ['nullable', 'date', 'after_or_equal:loan_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'signature_data' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.warehouse_item_id' => ['required', 'exists:warehouse_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $signaturePath = $this->saveSignature($request->input('signature_data'));
        $borrowerName = $request->string('borrower_name')->trim()->value();
        $purpose = $request->string('purpose')->trim()->value();

        try {
            $loan = DB::transaction(function () use ($request, $signaturePath, $borrowerName, $purpose) {
                $loan = WarehouseLoan::create([
                    'loan_number' => WarehouseLoan::generateLoanNumber(),
                    'borrower_name' => $borrowerName,
                    'borrower_contact' => $request->filled('borrower_contact') ? $request->string('borrower_contact')->trim()->value() : null,
                    'purpose' => $purpose,
                    'loan_date' => $request->input('loan_date'),
                    'expected_return_date' => $request->input('expected_return_date') ?: null,
                    'status' => WarehouseLoanStatus::BORROWED,
                    'notes' => $request->input('notes'),
                    'signature_path' => $signaturePath,
                    'user_id' => auth()->id(),
                ]);

                foreach ($request->input('items') as $itemPayload) {
                    /** @var WarehouseItem $item */
                    $item = WarehouseItem::lockForUpdate()->findOrFail($itemPayload['warehouse_item_id']);
                    $qty = (int) $itemPayload['quantity'];

                    if ($qty > $item->current_stock) {
                        throw ValidationException::withMessages([
                            'items' => "Stok untuk barang '{$item->name}' tidak mencukupi (Tersedia: {$item->current_stock} {$item->unit}, Diminta: {$qty} {$item->unit}).",
                        ]);
                    }

                    // Buat relasi item pinjaman
                    $loan->items()->create([
                        'warehouse_item_id' => $item->id,
                        'quantity_loaned' => $qty,
                        'quantity_returned' => 0,
                        'notes' => $itemPayload['notes'] ?? null,
                    ]);

                    // Kurangi stok barang di gudang & catat pergerakan
                    $item->removeStock(
                        quantity: $qty,
                        notes: "Peminjaman #{$loan->loan_number} ({$purpose})",
                        picName: $borrowerName,
                        signaturePath: $signaturePath
                    );
                }

                return $loan;
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Transaksi peminjaman #{$loan->loan_number} berhasil dicatat.",
                    'loan' => [
                        'id' => $loan->id,
                        'loan_number' => $loan->loan_number,
                        'borrower_name' => $loan->borrower_name,
                        'purpose' => $loan->purpose,
                        'total_quantity' => $loan->totalQuantityLoaned(),
                        'url' => route('stin.loans.show', $loan),
                    ],
                    'log' => [
                        'time' => now()->timezone('Asia/Jakarta')->format('H:i:s'),
                        'sku' => $loan->loan_number,
                        'name' => "Pinjam: {$loan->borrower_name} ({$loan->purpose})",
                        'action_type' => 'loan',
                        'quantity' => $loan->totalQuantityLoaned(),
                        'current_stock' => '-',
                        'unit' => 'unit',
                        'pic_name' => $loan->borrower_name,
                    ],
                ]);
            }

            return redirect()->route('stin.loans.show', $loan)
                ->with('success', "Transaksi peminjaman #{$loan->loan_number} berhasil dicatat. Stok fisik telah disesuaikan.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mencatat peminjaman: '.$e->getMessage(),
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Gagal mencatat peminjaman: '.$e->getMessage()]);
        }
    }

    /**
     * Tampilkan detail peminjaman, progress pengembalian, dan riwayat.
     */
    public function show(WarehouseLoan $loan): View
    {
        $loan->load([
            'items.item',
            'returns.items.item',
            'returns.user',
            'user',
        ]);

        return view('stin.loans.show', compact('loan'));
    }

    /**
     * Tampilkan form proses pengembalian barang.
     */
    public function returnForm(WarehouseLoan $loan): View|RedirectResponse
    {
        if ($loan->status === WarehouseLoanStatus::RETURNED) {
            return redirect()->route('stin.loans.show', $loan)
                ->with('info', "Seluruh barang pada transaksi #{$loan->loan_number} sudah dikembalikan lengkap.");
        }

        $loan->load(['items.item']);

        return view('stin.loans.return', compact('loan'));
    }

    /**
     * Proses pengembalian barang (penuh atau bertahap/parsial).
     */
    public function processReturn(Request $request, WarehouseLoan $loan): RedirectResponse
    {
        if ($loan->status === WarehouseLoanStatus::RETURNED) {
            return redirect()->route('stin.loans.show', $loan)
                ->with('info', "Seluruh barang pada transaksi #{$loan->loan_number} sudah berstatus lunas/kembali.");
        }

        $request->validate([
            'receiver_name' => ['required', 'string', 'max:255'],
            'return_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'signature_data' => ['nullable', 'string'],
            'returns' => ['required', 'array', 'min:1'],
            'returns.*.warehouse_loan_item_id' => ['required', 'exists:warehouse_loan_items,id'],
            'returns.*.quantity' => ['required', 'integer', 'min:0'],
            'returns.*.condition' => ['nullable', 'in:good,damaged,lost'],
            'returns.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $returnsPayload = $request->input('returns');
        $totalReturning = 0;
        foreach ($returnsPayload as $entry) {
            $totalReturning += (int) ($entry['quantity'] ?? 0);
        }

        if ($totalReturning <= 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['returns' => 'Kuantitas pengembalian minimal 1 unit untuk setidaknya satu barang.']);
        }

        $signaturePath = $this->saveSignature($request->input('signature_data'));
        $receiverName = $request->string('receiver_name')->trim()->value();

        try {
            DB::transaction(function () use ($loan, $request, $returnsPayload, $signaturePath, $receiverName) {
                $returnRecord = WarehouseLoanReturn::create([
                    'warehouse_loan_id' => $loan->id,
                    'return_number' => WarehouseLoanReturn::generateReturnNumber(),
                    'return_date' => $request->input('return_date'),
                    'receiver_name' => $receiverName,
                    'signature_path' => $signaturePath,
                    'notes' => $request->input('notes'),
                    'user_id' => auth()->id(),
                ]);

                foreach ($returnsPayload as $entry) {
                    $qtyToReturn = (int) ($entry['quantity'] ?? 0);
                    if ($qtyToReturn <= 0) {
                        continue;
                    }

                    /** @var WarehouseLoanItem $loanItem */
                    $loanItem = WarehouseLoanItem::lockForUpdate()->where('warehouse_loan_id', $loan->id)
                        ->findOrFail($entry['warehouse_loan_item_id']);

                    $remaining = $loanItem->remainingQuantity();
                    if ($qtyToReturn > $remaining) {
                        throw ValidationException::withMessages([
                            'returns' => "Jumlah barang '{$loanItem->item->name}' yang dikembalikan ({$qtyToReturn}) melebihi sisa pinjaman ({$remaining}).",
                        ]);
                    }

                    // Catat item pengembalian
                    $returnRecord->items()->create([
                        'warehouse_loan_item_id' => $loanItem->id,
                        'warehouse_item_id' => $loanItem->warehouse_item_id,
                        'quantity_returned' => $qtyToReturn,
                        'condition' => $entry['condition'] ?? 'good',
                        'notes' => $entry['notes'] ?? null,
                    ]);

                    // Perbarui kuantitas kembali pada item pinjaman
                    $loanItem->increment('quantity_returned', $qtyToReturn);

                    // Kembalikan stok fisik ke gudang & catat log mutasi
                    /** @var WarehouseItem $warehouseItem */
                    $warehouseItem = WarehouseItem::lockForUpdate()->findOrFail($loanItem->warehouse_item_id);
                    $conditionNote = ! empty($entry['condition']) && $entry['condition'] !== 'good' ? " [Kondisi: {$entry['condition']}]" : '';
                    $warehouseItem->addStock(
                        quantity: $qtyToReturn,
                        notes: "Pengembalian #{$loan->loan_number} ({$returnRecord->return_number}){$conditionNote}",
                        picName: $receiverName,
                        signaturePath: $signaturePath
                    );
                }

                // Perbarui status peminjaman secara menyeluruh
                $loan->refreshStatus();
            });

            return redirect()->route('stin.loans.show', $loan)
                ->with('success', 'Pengembalian barang berhasil diproses. Stok fisik gudang telah bertambah kembali.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memproses pengembalian: '.$e->getMessage()]);
        }
    }

    /**
     * Simpan file tanda tangan digital dari base64.
     */
    private function saveSignature(?string $signatureData): ?string
    {
        if (empty($signatureData) || ! str_contains($signatureData, 'base64,')) {
            return null;
        }

        $base64Data = explode('base64,', $signatureData)[1] ?? null;
        if (! $base64Data) {
            return null;
        }

        $fileName = 'signatures/sig_loan_'.uniqid().'_'.time().'.png';
        Storage::disk('public')->put($fileName, base64_decode($base64Data));

        return $fileName;
    }
}
