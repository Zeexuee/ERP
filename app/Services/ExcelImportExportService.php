<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MaterialLog;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelImportExportService
{
    public function __construct(protected SalesOrderService $salesOrderService) {}

    // ==========================================
    // 1. CUSTOMERS IMPORT & EXPORT
    // ==========================================

    public function exportCustomers(): StreamedResponse
    {
        $filename = 'customers_export_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID Pelanggan',
                'Nama Pelanggan',
                'Email',
                'No. Telepon',
                'Alamat',
                'Status Akun',
                'Total Pesanan (SO)',
                'Total Akumulasi Pembelian (Rp)',
                'Rincian Riwayat Pembelian (SO & Item Produk)',
                'Total Tagihan Faktur (Rp)',
                'Total Nominal Sudah Dibayar (Rp)',
                'Sisa Piutang Belum Lunas (Rp)',
                'Rincian Riwayat Pembayaran (No. Bukti, Tgl, Nominal, PIC)',
                'Tanggal Terdaftar',
            ]);

            Customer::with([
                'salesOrders.items.product',
                'salesOrders.invoices.payments',
            ])->chunk(100, function ($customers) use ($handle) {
                foreach ($customers as $c) {
                    $totalSpent = $c->salesOrders->sum('total_amount');
                    $allInvoices = $c->getAllInvoices();
                    $totalInvoiced = $allInvoices->sum('total_amount');
                    $totalPaid = $allInvoices->flatMap->payments->sum('amount');
                    $outstanding = max(0, $totalInvoiced - $totalPaid);

                    // Detailed purchase history string
                    $purchaseHistory = $c->salesOrders->map(function ($so) {
                        $itemsStr = $so->items->map(function ($item) {
                            $prodName = $item->product ? $item->product->name : 'Produk';
                            $unit = $item->unit ?? 'kg';

                            return "{$prodName} ({$item->quantity} {$unit} @ Rp ".number_format($item->unit_price, 0, ',', '.').')';
                        })->implode(', ');

                        return "[{$so->order_number} ({$so->created_at->format('d/m/Y')}) Status: ".strtoupper($so->status->value)." - Items: {$itemsStr} | Total: Rp ".number_format($so->total_amount, 0, ',', '.').']';
                    })->implode(";\n");

                    // Detailed payment history string
                    $allPayments = $allInvoices->flatMap->payments->sortByDesc('payment_date');
                    $paymentHistory = $allPayments->map(function ($p) {
                        $invNumber = $p->invoice ? $p->invoice->invoice_number : '-';
                        $pic = $p->pic_name ? "PIC: {$p->pic_name}" : 'PIC: -';

                        return "[{$p->payment_number} ({$p->payment_date->format('d/m/Y')}) Faktur: {$invNumber} - Rp ".number_format($p->amount, 0, ',', '.')." via {$p->payment_method} ({$pic})]";
                    })->implode(";\n");

                    fputcsv($handle, [
                        $c->id,
                        $c->name,
                        $c->email ?? '-',
                        $c->phone ?? '-',
                        $c->address ?? '-',
                        $c->is_active ? 'Aktif' : 'Non-Aktif',
                        $c->salesOrders->count(),
                        $totalSpent,
                        ! empty($purchaseHistory) ? $purchaseHistory : 'Belum ada pembelian',
                        $totalInvoiced,
                        $totalPaid,
                        $outstanding,
                        ! empty($paymentHistory) ? $paymentHistory : 'Belum ada pembayaran',
                        $c->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function downloadCustomerTemplate(): StreamedResponse
    {
        $filename = 'template_import_customers.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['nama_pelanggan', 'email', 'telepon', 'alamat', 'status_aktif']);
            fputcsv($handle, ['PT Sumber Pangan Makmur', 'admin@sumberpangan.com', '08123456789', 'Jl. Industri Raya Blok A No. 10', '1']);
            fputcsv($handle, ['CV Agro Mandiri', 'kontak@agromandiri.co.id', '08198765432', 'Jl. Raya Pergudangan No. 5', '1']);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function importCustomers(UploadedFile $file): array
    {
        $rows = $this->parseCsvFile($file);
        if (empty($rows)) {
            return ['success' => 0, 'errors' => ['Berkas CSV / Excel kosong atau format tidak sesuai.']];
        }

        $successCount = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $lineNum = $index + 2;
            $name = trim($row['nama_pelanggan'] ?? $row['name'] ?? $row[0] ?? '');
            $email = trim($row['email'] ?? $row[1] ?? '');
            $phone = trim($row['telepon'] ?? $row['phone'] ?? $row[2] ?? '');
            $address = trim($row['alamat'] ?? $row['address'] ?? $row[3] ?? '');
            $isActive = trim($row['status_aktif'] ?? $row['is_active'] ?? $row[4] ?? '1');

            if (empty($name)) {
                $errors[] = "Baris {$lineNum}: Nama pelanggan tidak boleh kosong.";

                continue;
            }

            try {
                Customer::create([
                    'name' => $name,
                    'email' => ! empty($email) ? $email : null,
                    'phone' => ! empty($phone) ? $phone : null,
                    'address' => ! empty($address) ? $address : null,
                    'is_active' => (bool) ($isActive === '1' || strtolower($isActive) === 'aktif' || strtolower($isActive) === 'active'),
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Baris {$lineNum} ({$name}): ".$e->getMessage();
            }
        }

        return ['success' => $successCount, 'errors' => $errors];
    }

    // ==========================================
    // 2. SALES ORDERS IMPORT & EXPORT
    // ==========================================

    public function exportSalesOrders(): StreamedResponse
    {
        $filename = 'sales_orders_export_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'No. Sales Order',
                'Tanggal SO',
                'Nama Pelanggan',
                'Email Pelanggan',
                'Telepon Pelanggan',
                'Penerima Pesanan / PIC Sales',
                'Status Sales Order',
                'Status Permintaan Produksi (No. PR)',
                'Detail Lengkap Item Produk (Nama, Qty, Satuan, Harga, Subtotal)',
                'Total Nilai Order (Rp)',
                'No. Faktur Terkait',
                'Total Tagihan Faktur (Rp)',
                'Total Sudah Terbayar (Rp)',
                'Sisa Tagihan (Rp)',
                'Status Pembayaran Faktur',
                'Rincian Riwayat Pembayaran Masuk',
            ]);

            SalesOrder::with(['customer', 'items.product', 'productionRequest', 'invoices.payments'])->chunk(100, function ($salesOrders) use ($handle) {
                foreach ($salesOrders as $so) {
                    $itemsDetail = $so->items->map(function ($item) {
                        $pName = $item->product ? $item->product->name : 'Produk';
                        $sku = $item->product && $item->product->sku ? " [{$item->product->sku}]" : '';
                        $unit = $item->unit ?? 'kg';

                        return "{$pName}{$sku} ({$item->quantity} {$unit} @ Rp ".number_format($item->unit_price, 0, ',', '.').' = Rp '.number_format($item->subtotal, 0, ',', '.').')';
                    })->implode(";\n");

                    $prInfo = $so->productionRequest
                        ? "{$so->productionRequest->request_number} [".strtoupper($so->productionRequest->status->value).']'
                        : 'Belum Ada';

                    $invNumbers = $so->invoices->pluck('invoice_number')->implode(', ') ?: '-';
                    $totalInvoiced = $so->invoices->sum('total_amount');
                    $totalPaid = $so->invoices->flatMap->payments->sum('amount');
                    $remaining = max(0, $totalInvoiced - $totalPaid);
                    $firstInvoice = $so->invoices->first();
                    $payStatus = $firstInvoice ? strtoupper($firstInvoice->status->value) : 'UNBILLED';

                    $paymentsHistory = $so->invoices->flatMap->payments->map(function ($p) {
                        $pic = $p->pic_name ? " - PIC: {$p->pic_name}" : '';

                        return "[{$p->payment_number} ({$p->payment_date->format('d/m/Y')}): Rp ".number_format($p->amount, 0, ',', '.')." via {$p->payment_method}{$pic}]";
                    })->implode(";\n");

                    fputcsv($handle, [
                        $so->order_number,
                        $so->created_at->format('Y-m-d H:i:s'),
                        $so->customer ? $so->customer->name : '-',
                        $so->customer ? ($so->customer->email ?? '-') : '-',
                        $so->customer ? ($so->customer->phone ?? '-') : '-',
                        $so->pic_name ?? '-',
                        strtoupper($so->status->value),
                        $prInfo,
                        ! empty($itemsDetail) ? $itemsDetail : '-',
                        $so->total_amount,
                        $invNumbers,
                        $totalInvoiced,
                        $totalPaid,
                        $remaining,
                        $payStatus,
                        ! empty($paymentsHistory) ? $paymentsHistory : 'Belum ada pembayaran',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function downloadSalesOrderTemplate(): StreamedResponse
    {
        $filename = 'template_import_sales_orders.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['nama_pelanggan', 'nama_produk', 'jumlah', 'satuan', 'harga_satuan', 'penerima_pesanan_pic']);
            fputcsv($handle, ['PT Sumber Pangan Makmur', 'Biji Kopi Arabika Grade A', '50', 'kg', '120000', 'Budi Santoso']);
            fputcsv($handle, ['CV Agro Mandiri', 'Bubuk Coklat Premium', '25', 'kg', '85000', 'Siti Rahma']);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function importSalesOrders(UploadedFile $file): array
    {
        $rows = $this->parseCsvFile($file);
        if (empty($rows)) {
            return ['success' => 0, 'errors' => ['Berkas CSV / Excel kosong atau format tidak sesuai.']];
        }

        $successCount = 0;
        $errors = [];

        // Group rows by customer & PIC to combine multiple items per SO if needed
        foreach ($rows as $index => $row) {
            $lineNum = $index + 2;
            $customerName = trim($row['nama_pelanggan'] ?? $row['customer_name'] ?? $row[0] ?? '');
            $productName = trim($row['nama_produk'] ?? $row['product_name'] ?? $row[1] ?? '');
            $quantity = (float) ($row['jumlah'] ?? $row['quantity'] ?? $row[2] ?? 1);
            $unit = trim($row['satuan'] ?? $row['unit'] ?? $row[3] ?? 'kg');
            $price = (float) ($row['harga_satuan'] ?? $row['unit_price'] ?? $row[4] ?? 0);
            $picName = trim($row['penerima_pesanan_pic'] ?? $row['pic_name'] ?? $row[5] ?? 'Admin Sales');

            if (empty($customerName)) {
                $errors[] = "Baris {$lineNum}: Nama pelanggan tidak boleh kosong.";

                continue;
            }

            if (empty($productName)) {
                $errors[] = "Baris {$lineNum}: Nama produk tidak boleh kosong.";

                continue;
            }

            try {
                DB::transaction(function () use ($customerName, $productName, $quantity, $unit, $price, $picName, &$successCount) {
                    // Resolve or create Customer
                    $customer = Customer::firstOrCreate(
                        ['name' => $customerName],
                        ['is_active' => true]
                    );

                    // Resolve or create Product
                    $product = Product::firstOrCreate(
                        ['name' => $productName],
                        [
                            'sku' => 'PRD-'.strtoupper(substr(uniqid(), -6)),
                            'price' => $price > 0 ? $price : 10000,
                            'unit' => ! empty($unit) ? $unit : 'kg',
                            'stock_quantity' => 0,
                        ]
                    );

                    $itemPrice = $price > 0 ? $price : $product->price;

                    // Create Sales Order via Service to trigger Production Request & Invoice
                    $this->salesOrderService->createSalesOrder([
                        'customer_id' => $customer->id,
                        'pic_name' => $picName,
                        'items' => [
                            [
                                'product_id' => $product->id,
                                'quantity' => $quantity > 0 ? $quantity : 1,
                                'unit' => ! empty($unit) ? $unit : ($product->unit ?? 'kg'),
                                'unit_price' => $itemPrice,
                            ],
                        ],
                    ]);

                    $successCount++;
                });
            } catch (\Exception $e) {
                $errors[] = "Baris {$lineNum} ({$customerName} - {$productName}): ".$e->getMessage();
            }
        }

        return ['success' => $successCount, 'errors' => $errors];
    }

    // ==========================================
    // 3. INVOICES IMPORT & EXPORT
    // ==========================================

    public function exportInvoices(): StreamedResponse
    {
        $filename = 'invoices_export_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'No. Faktur',
                'No. Sales Order',
                'Tanggal Terbit Faktur',
                'Jatuh Tempo',
                'Nama Pelanggan',
                'Email Pelanggan',
                'Telepon Pelanggan',
                'Alamat Pelanggan',
                'Penerima Pesanan (PIC SO)',
                'Detail Item Produk yang Ditagihkan (Nama, Qty, Satuan, Harga, Subtotal)',
                'Total Tagihan (Rp)',
                'Total Sudah Dibayar (Rp)',
                'Sisa Saldo Tagihan (Rp)',
                'Status Faktur',
                'Rincian Riwayat Pembayaran (No. Bukti, Tgl, Nominal, Metode, PIC)',
            ]);

            Invoice::with(['salesOrder.customer', 'salesOrder.items.product', 'payments'])->chunk(100, function ($invoices) use ($handle) {
                foreach ($invoices as $inv) {
                    $paidAmount = $inv->payments->sum('amount');
                    $remaining = max(0, $inv->total_amount - $paidAmount);
                    $so = $inv->salesOrder;
                    $cust = $so ? $so->customer : null;

                    // Detailed items
                    $itemsDetail = $so ? $so->items->map(function ($item) {
                        $pName = $item->product ? $item->product->name : 'Produk';
                        $unit = $item->unit ?? 'kg';

                        return "{$pName} ({$item->quantity} {$unit} @ Rp ".number_format($item->unit_price, 0, ',', '.').' = Rp '.number_format($item->subtotal, 0, ',', '.').')';
                    })->implode(";\n") : '-';

                    // Detailed payments
                    $paymentsDetail = $inv->payments->map(function ($p) {
                        $pic = $p->pic_name ? " - PIC: {$p->pic_name}" : '';
                        $proof = $p->proof_file ? ' [Ada Bukti]' : '';

                        return "[{$p->payment_number} ({$p->payment_date->format('d/m/Y')}): Rp ".number_format($p->amount, 0, ',', '.')." via {$p->payment_method}{$pic}{$proof}]";
                    })->implode(";\n");

                    fputcsv($handle, [
                        $inv->invoice_number,
                        $so ? $so->order_number : '-',
                        $inv->created_at->format('Y-m-d H:i:s'),
                        $inv->due_date->format('Y-m-d'),
                        $cust ? $cust->name : '-',
                        $cust ? ($cust->email ?? '-') : '-',
                        $cust ? ($cust->phone ?? '-') : '-',
                        $cust ? ($cust->address ?? '-') : '-',
                        $so ? ($so->pic_name ?? '-') : '-',
                        ! empty($itemsDetail) ? $itemsDetail : '-',
                        $inv->total_amount,
                        $paidAmount,
                        $remaining,
                        strtoupper($inv->status->value),
                        ! empty($paymentsDetail) ? $paymentsDetail : 'Belum ada pembayaran',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function downloadInvoiceTemplate(): StreamedResponse
    {
        $filename = 'template_import_invoices.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['nomor_sales_order', 'total_tagihan', 'jatuh_tempo_yyyy_mm_dd', 'status_faktur']);
            fputcsv($handle, ['SO-20260831-ABCD', '5000000', date('Y-m-d', strtotime('+30 days')), 'unpaid']);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function importInvoices(UploadedFile $file): array
    {
        $rows = $this->parseCsvFile($file);
        if (empty($rows)) {
            return ['success' => 0, 'errors' => ['Berkas CSV / Excel kosong atau format tidak sesuai.']];
        }

        $successCount = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $lineNum = $index + 2;
            $soNumber = trim($row['nomor_sales_order'] ?? $row['order_number'] ?? $row[0] ?? '');
            $totalAmount = (float) ($row['total_tagihan'] ?? $row['total_amount'] ?? $row[1] ?? 0);
            $dueDate = trim($row['jatuh_tempo_yyyy_mm_dd'] ?? $row['due_date'] ?? $row[2] ?? '');
            $statusStr = strtolower(trim($row['status_faktur'] ?? $row['status'] ?? $row[3] ?? 'unpaid'));

            if (empty($soNumber)) {
                $errors[] = "Baris {$lineNum}: Nomor Sales Order tidak boleh kosong.";

                continue;
            }

            $salesOrder = SalesOrder::where('order_number', $soNumber)->first();
            if (! $salesOrder) {
                $errors[] = "Baris {$lineNum}: Sales Order '{$soNumber}' tidak ditemukan di sistem.";

                continue;
            }

            try {
                $invoiceNumber = 'INV-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));
                $statusEnum = match ($statusStr) {
                    'paid', 'lunas' => InvoiceStatus::PAID,
                    'partial', 'sebagian' => InvoiceStatus::PARTIAL,
                    default => InvoiceStatus::UNPAID,
                };

                Invoice::create([
                    'sales_order_id' => $salesOrder->id,
                    'invoice_number' => $invoiceNumber,
                    'status' => $statusEnum,
                    'total_amount' => $totalAmount > 0 ? $totalAmount : $salesOrder->total_amount,
                    'due_date' => ! empty($dueDate) ? Carbon::parse($dueDate)->toDateString() : Carbon::now()->addDays(30)->toDateString(),
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Baris {$lineNum} ({$soNumber}): ".$e->getMessage();
            }
        }

        return ['success' => $successCount, 'errors' => $errors];
    }

    // ==========================================
    // 4. MATERIAL LOGS EXPORT
    // ==========================================

    public function exportMaterialLogs(): StreamedResponse
    {
        $filename = 'riwayat_alur_barang_gudang_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Jenis Alur',
                'No. Referensi',
                'Kode Bahan',
                'Nama Bahan Baku',
                'Kuantitas',
                'Satuan',
                'Asal / Tujuan / Keterangan',
                'Tanggal & Waktu',
                'Petugas',
                'Catatan',
            ]);

            MaterialLog::with('material')
                ->latest('id')
                ->take(60)
                ->get()
                ->each(function ($log) use ($handle) {
                    $typeStr = match ($log->type) {
                        'in' => 'Barang Masuk',
                        'out' => 'Pemakaian Produksi / Sortir',
                        default => 'Timbang Ulang',
                    };

                    $qtyFormatted = match ($log->type) {
                        'in' => "+{$log->quantity}",
                        'out' => "-{$log->quantity}",
                        default => "{$log->quantity} (Opname)",
                    };

                    fputcsv($handle, [
                        $typeStr,
                        $log->reference_number ?? '-',
                        $log->material ? $log->material->code : '-',
                        $log->material ? $log->material->name : '-',
                        $qtyFormatted,
                        $log->unit,
                        $log->source_or_destination ?? '-',
                        $log->movement_date ? $log->movement_date->format('d/m/Y H:i').' WIB' : '-',
                        $log->actor_by ?? '-',
                        $log->notes ?? '-',
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ==========================================
    // HELPER: PARSE CSV / SPREADSHEET FILES
    // ==========================================

    protected function parseCsvFile(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        $rows = [];
        $header = null;

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            // Check if line was split by semicolon instead of comma
            if (count($data) === 1 && str_contains($data[0], ';')) {
                $data = str_getcsv($data[0], ';');
            }

            // Remove UTF-8 BOM from the first item of header
            if ($header === null) {
                if (isset($data[0])) {
                    $data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
                }
                $header = array_map(function ($h) {
                    return strtolower(trim(str_replace([' ', '-', '.'], '_', $h)));
                }, $data);

                continue;
            }

            if (count(array_filter($data)) === 0) {
                continue;
            }

            // Map associative array if header columns match
            if (count($header) === count($data)) {
                $rows[] = array_combine($header, $data);
            } else {
                $rows[] = $data;
            }
        }

        fclose($handle);

        return $rows;
    }
}
