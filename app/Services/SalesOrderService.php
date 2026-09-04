<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\ProductionRequestStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductionRequest;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalesOrderService
{
    /**
     * Create a new Sales Order directly.
     */
    public function createSalesOrder(array $data): SalesOrder
    {
        return DB::transaction(function () use ($data) {
            $orderNumber = 'SO-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));

            $salesOrder = SalesOrder::create([
                'customer_id' => $data['customer_id'],
                'order_number' => $orderNumber,
                'status' => SalesOrderStatus::PROCESSING,
                'total_amount' => 0,
                'pic_name' => $data['pic_name'] ?? null,
                'signature' => $data['signature'] ?? null,
            ]);

            $totalAmount = 0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitPrice = $item['unit_price'] ?? $product->price;
                $quantity = $item['quantity'];
                $unit = $item['unit'] ?? $product->unit ?? 'kg';
                $subtotal = $unitPrice * $quantity;

                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
            }

            $salesOrder->update(['total_amount' => $totalAmount]);

            // Automatically create Production Request
            $this->triggerProductionRequest($salesOrder);

            // Automatically create Invoice
            $this->generateInvoice($salesOrder);

            return $salesOrder->load(['customer', 'items.product', 'productionRequest', 'invoices']);
        });
    }

    /**
     * Update status with state machine transition rules.
     * Transitions:
     * - draft -> confirmed
     * - confirmed -> processing | ready | cancelled
     * - processing -> ready | cancelled
     * - ready -> completed | cancelled
     */
    public function updateStatus(SalesOrder $salesOrder, SalesOrderStatus $newStatus): SalesOrder
    {
        $currentStatus = $salesOrder->status;

        $validTransitions = [
            SalesOrderStatus::DRAFT->value => [SalesOrderStatus::CONFIRMED, SalesOrderStatus::CANCELLED],
            SalesOrderStatus::CONFIRMED->value => [SalesOrderStatus::PROCESSING, SalesOrderStatus::READY, SalesOrderStatus::CANCELLED],
            SalesOrderStatus::PROCESSING->value => [SalesOrderStatus::READY, SalesOrderStatus::CANCELLED],
            SalesOrderStatus::READY->value => [SalesOrderStatus::COMPLETED, SalesOrderStatus::CANCELLED],
            SalesOrderStatus::COMPLETED->value => [],
            SalesOrderStatus::CANCELLED->value => [],
        ];

        $allowedTransitions = $validTransitions[$currentStatus->value] ?? [];

        if (! in_array($newStatus, $allowedTransitions, true)) {
            throw new InvalidArgumentException(
                "Invalid status transition from '{$currentStatus->value}' to '{$newStatus->value}'."
            );
        }

        return DB::transaction(function () use ($salesOrder, $newStatus) {
            $salesOrder->update(['status' => $newStatus]);

            // If transitioning to PROCESSING, automatically trigger a Production Request if not already triggered
            if ($newStatus === SalesOrderStatus::PROCESSING && ! $salesOrder->productionRequest) {
                $this->triggerProductionRequest($salesOrder);
            }

            return $salesOrder->fresh();
        });
    }

    /**
     * Trigger a Production Request for a Sales Order.
     */
    public function triggerProductionRequest(SalesOrder $salesOrder): ProductionRequest
    {
        if ($salesOrder->productionRequest) {
            return $salesOrder->productionRequest;
        }

        $requestNumber = 'PR-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));

        return ProductionRequest::create([
            'sales_order_id' => $salesOrder->id,
            'request_number' => $requestNumber,
            'status' => ProductionRequestStatus::PENDING,
            'requested_date' => Carbon::now(),
        ]);
    }

    /**
     * Generate an Invoice for a Sales Order.
     */
    public function generateInvoice(SalesOrder $salesOrder, ?string $dueDate = null): Invoice
    {
        if (in_array($salesOrder->status, [SalesOrderStatus::DRAFT, SalesOrderStatus::CANCELLED], true)) {
            throw new InvalidArgumentException('Cannot generate invoice for a Sales Order in DRAFT or CANCELLED status.');
        }

        return DB::transaction(function () use ($salesOrder, $dueDate) {
            $invoiceNumber = 'INV-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));

            return Invoice::create([
                'sales_order_id' => $salesOrder->id,
                'invoice_number' => $invoiceNumber,
                'status' => InvoiceStatus::UNPAID,
                'total_amount' => $salesOrder->total_amount,
                'due_date' => $dueDate ?? Carbon::now()->addDays(30)->toDateString(),
            ]);
        });
    }
}
