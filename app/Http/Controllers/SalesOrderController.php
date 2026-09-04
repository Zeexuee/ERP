<?php

namespace App\Http\Controllers;

use App\Enums\SalesOrderStatus;
use App\Http\Requests\SalesOrder\StoreSalesOrderRequest;
use App\Http\Requests\SalesOrder\UpdateSalesOrderStatusRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\ExcelImportExportService;
use App\Services\SalesOrderService;
use Exception;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public function __construct(protected SalesOrderService $salesOrderService) {}

    public function index()
    {
        $salesOrders = SalesOrder::with(['customer', 'productionRequest', 'invoices'])->latest()->paginate(10);

        return view('sales-orders.index', compact('salesOrders'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->get();
        $products = Product::all();

        return view('sales-orders.create', compact('customers', 'products'));
    }

    public function store(StoreSalesOrderRequest $request)
    {
        $salesOrder = $this->salesOrderService->createSalesOrder($request->validated());

        return redirect()->route('sales-orders.show', $salesOrder)
            ->with('success', "Sales Order {$salesOrder->order_number} berhasil dibuat! Permintaan produksi dan faktur invoice telah diterbitkan secara otomatis.");
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.product', 'productionRequest', 'invoices.payments']);
        $statuses = SalesOrderStatus::cases();

        return view('sales-orders.show', compact('salesOrder', 'statuses'));
    }

    public function updateStatus(UpdateSalesOrderStatusRequest $request, SalesOrder $salesOrder)
    {
        try {
            $newStatus = SalesOrderStatus::from($request->validated()['status']);
            $this->salesOrderService->updateStatus($salesOrder, $newStatus);

            return back()->with('success', 'Status Sales Order berhasil diperbarui menjadi '.strtoupper($newStatus->value));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function generateInvoice(Request $request, SalesOrder $salesOrder)
    {
        $request->validate(['due_date' => 'required|date|after_or_equal:today']);

        try {
            $invoice = $this->salesOrderService->generateInvoice($salesOrder, $request->input('due_date'));

            return redirect()->route('invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} berhasil dibuat dari Sales Order!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function exportExcel(ExcelImportExportService $excelService)
    {
        return $excelService->exportSalesOrders();
    }

    public function templateExcel(ExcelImportExportService $excelService)
    {
        return $excelService->downloadSalesOrderTemplate();
    }

    public function importExcel(Request $request, ExcelImportExportService $excelService)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        $result = $excelService->importSalesOrders($request->file('file'));

        if ($result['success'] > 0) {
            $msg = "Berhasil mengimpor {$result['success']} Sales Order beserta rincian item, permintaan produksi, dan faktur!";
            if (! empty($result['errors'])) {
                $msg .= ' Beberapa baris dilewati karena data tidak valid: '.implode(' ', array_slice($result['errors'], 0, 3));
            }

            return redirect()->route('sales-orders.index')->with('success', $msg);
        }

        return redirect()->route('sales-orders.index')->with('error', 'Gagal mengimpor Sales Order: '.implode(' | ', $result['errors']));
    }
}
