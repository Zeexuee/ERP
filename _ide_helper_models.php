<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read float $total_outstanding_balance
 * @property-read float $total_purchases_amount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesOrder> $salesOrders
 * @property-read int|null $sales_orders_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer withoutTrashed()
 */
	class Customer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $sales_order_id
 * @property string $invoice_number
 * @property \App\Enums\InvoiceStatus $status
 * @property numeric $total_amount
 * @property \Illuminate\Support\Carbon $due_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $paid_amount
 * @property-read float $remaining_amount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\SalesOrder $salesOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSalesOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 */
	class Invoice extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $category
 * @property string $unit
 * @property numeric $stock_quantity
 * @property numeric $minimum_stock
 * @property numeric $unit_cost
 * @property string|null $source_tembak_code
 * @property \Illuminate\Support\Carbon|null $last_weighed_at
 * @property string|null $last_weighed_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialBranch> $branches
 * @property-read int|null $branches_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialDryingLog> $dryingLogs
 * @property-read int|null $drying_logs_count
 * @property-read string $branch_label
 * @property-read float $branched_quantity
 * @property-read float $unbranched_quantity
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialLog> $logs
 * @property-read int|null $logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialReceipt> $receipts
 * @property-read int|null $receipts_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereLastWeighedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereLastWeighedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereMinimumStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereSourceTembakCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereStockQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Material whereUpdatedAt($value)
 */
	class Material extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $material_id
 * @property string $branch_code
 * @property numeric $quantity
 * @property string $source_type
 * @property string|null $source_reference
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $lot_label
 * @property-read string $source_label
 * @property-read \App\Models\Material $material
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialBranch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialBranch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialBranch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialBranch whereBranchCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialBranch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialBranch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialBranch whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialBranch whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialBranch whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialBranch whereSourceReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialBranch whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialBranch whereUpdatedAt($value)
 */
	class MaterialBranch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $carve_code
 * @property string $status
 * @property int $source_material_id
 * @property numeric $initial_weight
 * @property numeric|null $dried_weight
 * @property numeric|null $drying_loss_weight
 * @property \Illuminate\Support\Carbon $carve_date
 * @property string $pic_name
 * @property \Illuminate\Support\Carbon|null $report_date
 * @property string|null $report_pic_name
 * @property string|null $notes
 * @property string|null $signature_path
 * @property string|null $report_signature_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialCarveItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Material $sourceMaterial
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereCarveCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereCarveDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereDriedWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereDryingLossWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereInitialWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereReportDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereReportPicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereReportSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereSourceMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereUpdatedAt($value)
 */
	class MaterialCarveBatch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $material_carve_batch_id
 * @property int $target_material_id
 * @property numeric $result_weight
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaterialCarveBatch $carveBatch
 * @property-read \App\Models\Material $targetMaterial
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereMaterialCarveBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereResultWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereTargetMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereUpdatedAt($value)
 */
	class MaterialCarveItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $material_id
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property numeric $previous_weight
 * @property numeric|null $new_weight
 * @property numeric|null $shrinkage_weight
 * @property string $pic_name
 * @property string|null $notes
 * @property string|null $signature_path
 * @property string|null $report_signature_path
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Material $material
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog whereNewWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog wherePreviousWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog whereReportSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog whereShrinkageWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialDryingLog whereUpdatedAt($value)
 */
	class MaterialDryingLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $material_id
 * @property string $type
 * @property string|null $reference_number
 * @property numeric $quantity
 * @property string $unit
 * @property string|null $actor_by
 * @property string|null $source_or_destination
 * @property \Illuminate\Support\Carbon $movement_date
 * @property string|null $notes
 * @property string|null $signature_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Material $material
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereActorBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereMovementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereSourceOrDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialLog whereUpdatedAt($value)
 */
	class MaterialLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $melt_code
 * @property int $source_material_id
 * @property numeric $initial_weight
 * @property int|null $target_material_id
 * @property numeric|null $initial_liquid_weight
 * @property numeric|null $current_weight
 * @property string $status
 * @property \Illuminate\Support\Carbon $melt_date
 * @property string $pic_name
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $report_date
 * @property string|null $report_pic_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $current_stock
 * @property-read mixed $evaporation_percentage
 * @property-read mixed $total_evaporated_weight
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialMeltLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\Material $sourceMaterial
 * @property-read \App\Models\Material|null $targetMaterial
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereCurrentWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereInitialLiquidWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereInitialWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereMeltCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereMeltDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereReportDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereReportPicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereSourceMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereTargetMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltBatch whereUpdatedAt($value)
 */
	class MaterialMeltBatch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $material_melt_batch_id
 * @property \Illuminate\Support\Carbon $weighed_date
 * @property numeric $previous_weight
 * @property numeric $new_weight
 * @property numeric $evaporated_weight
 * @property string $pic_name
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaterialMeltBatch $batch
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog whereEvaporatedWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog whereMaterialMeltBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog whereNewWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog wherePreviousWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialMeltLog whereWeighedDate($value)
 */
	class MaterialMeltLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $receipt_number
 * @property int $material_id
 * @property numeric $quantity
 * @property numeric|null $ordered_quantity
 * @property string $unit
 * @property numeric|null $unit_cost
 * @property string $source_or_supplier
 * @property \Illuminate\Support\Carbon $received_date
 * @property string $received_by
 * @property string|null $notes
 * @property string|null $signature_path
 * @property string|null $image_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Material $material
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereOrderedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereReceiptNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereReceivedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereReceivedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereSourceOrSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialReceipt whereUpdatedAt($value)
 */
	class MaterialReceipt extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $sort_code
 * @property string $status
 * @property int $source_material_id
 * @property numeric $initial_weight
 * @property numeric|null $dried_weight
 * @property numeric|null $drying_loss_weight
 * @property \Illuminate\Support\Carbon $sort_date
 * @property \Illuminate\Support\Carbon|null $report_date
 * @property string $pic_name
 * @property string|null $report_pic_name
 * @property string|null $notes
 * @property string|null $signature_path
 * @property string|null $report_signature_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaterialSortItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Material $sourceMaterial
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereDriedWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereDryingLossWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereInitialWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereReportDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereReportPicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereReportSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereSortCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereSortDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereSourceMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortBatch whereUpdatedAt($value)
 */
	class MaterialSortBatch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $material_sort_batch_id
 * @property int $target_material_id
 * @property numeric $result_weight
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaterialSortBatch $sortBatch
 * @property-read \App\Models\Material $targetMaterial
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortItem whereMaterialSortBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortItem whereResultWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortItem whereTargetMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialSortItem whereUpdatedAt($value)
 */
	class MaterialSortItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $invoice_id
 * @property string $payment_number
 * @property numeric $amount
 * @property string $payment_method
 * @property \Illuminate\Support\Carbon $payment_date
 * @property string|null $proof_file
 * @property string|null $pic_name
 * @property string|null $signature
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Invoice $invoice
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereProofFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $sku
 * @property string $name
 * @property numeric $price
 * @property int $stock_quantity
 * @property string $unit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductBranch> $branches
 * @property-read int|null $branches_count
 * @property-read int $total_stock
 * @property-read float $total_weight
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesOrderItem> $salesOrderItems
 * @property-read int|null $sales_order_items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStockQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 */
	class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $product_id
 * @property string $branch_name
 * @property string|null $branch_code
 * @property int $quantity
 * @property numeric|null $weight
 * @property string|null $specification_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBranch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBranch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBranch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBranch whereBranchCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBranch whereBranchName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBranch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBranch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBranch whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBranch whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBranch whereSpecificationNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBranch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBranch whereWeight($value)
 */
	class ProductBranch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $batch_code
 * @property string $process_type
 * @property \Illuminate\Support\Carbon $coloring_date
 * @property string $pic_name
 * @property string|null $notes
 * @property string|null $signature_path
 * @property int $material_branch_id
 * @property numeric $total_weight
 * @property numeric|null $result_weight
 * @property numeric|null $loss_weight
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $report_date
 * @property string|null $report_pic_name
 * @property string|null $report_notes
 * @property string|null $report_signature_path
 * @property string|null $report_image_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaterialBranch $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductionColoringMaterial> $materials
 * @property-read int|null $materials_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereBatchCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereColoringDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereLossWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereMaterialBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereProcessType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereReportDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereReportImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereReportNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereReportPicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereReportSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereResultWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereTotalWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringBatch whereUpdatedAt($value)
 */
	class ProductionColoringBatch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $production_coloring_batch_id
 * @property int $material_id
 * @property numeric $weight
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProductionColoringBatch $batch
 * @property-read \App\Models\Material $material
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringMaterial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringMaterial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringMaterial query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringMaterial whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringMaterial whereProductionColoringBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringMaterial whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionColoringMaterial whereWeight($value)
 */
	class ProductionColoringMaterial extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $finishing_code
 * @property string|null $branch_code
 * @property bool $branch_is_merged
 * @property array<array-key, mixed>|null $source_branch_codes
 * @property int $source_material_id
 * @property numeric $initial_weight
 * @property numeric $current_weight
 * @property numeric|null $output_weight
 * @property string $status
 * @property int|null $product_id
 * @property int|null $product_branch_id
 * @property int|null $output_quantity
 * @property \Illuminate\Support\Carbon $finishing_date
 * @property \Illuminate\Support\Carbon|null $completed_date
 * @property string $pic_name
 * @property string|null $completion_pic_name
 * @property string|null $notes
 * @property string|null $completion_notes
 * @property string|null $signature_path
 * @property string|null $completion_signature_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $branch_label
 * @property-read string|null $cook_code
 * @property-read string $finishing_label
 * @property-read string $status_label
 * @property-read float $total_material_cost
 * @property-read float $total_waste_weight
 * @property-read float $total_weight_loss
 * @property-read \App\Models\ProductionFinishingStep|null $latestStep
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\ProductBranch|null $productBranch
 * @property-read \App\Models\Material $sourceMaterial
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductionFinishingStep> $steps
 * @property-read int|null $steps_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereBranchCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereBranchIsMerged($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereCompletedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereCompletionNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereCompletionPicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereCompletionSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereCurrentWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereFinishingCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereFinishingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereInitialWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereOutputQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereOutputWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereProductBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereSourceBranchCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereSourceMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingSession whereUpdatedAt($value)
 */
	class ProductionFinishingSession extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $production_finishing_session_id
 * @property int $sequence
 * @property \App\Enums\FinishingProcessType $process_type
 * @property \Illuminate\Support\Carbon $step_date
 * @property numeric $weight_before
 * @property numeric $weight_after
 * @property numeric $waste_weight
 * @property int|null $waste_material_id
 * @property numeric $material_cost
 * @property string $pic_name
 * @property string|null $notes
 * @property string|null $signature_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $unrecovered_loss
 * @property-read float $weight_loss
 * @property-read \App\Models\ProductionFinishingSession $session
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductionFinishingStepMaterial> $stepMaterials
 * @property-read int|null $step_materials_count
 * @property-read \App\Models\Material|null $wasteMaterial
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereMaterialCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereProcessType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereProductionFinishingSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereStepDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereWasteMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereWasteWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereWeightAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStep whereWeightBefore($value)
 */
	class ProductionFinishingStep extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $production_finishing_step_id
 * @property int $material_id
 * @property numeric $quantity
 * @property string $unit
 * @property numeric $unit_cost
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $total_cost
 * @property-read \App\Models\Material $material
 * @property-read \App\Models\ProductionFinishingStep $step
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStepMaterial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStepMaterial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStepMaterial query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStepMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStepMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStepMaterial whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStepMaterial whereProductionFinishingStepId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStepMaterial whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStepMaterial whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStepMaterial whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionFinishingStepMaterial whereUpdatedAt($value)
 */
	class ProductionFinishingStepMaterial extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $sales_order_id
 * @property string $request_number
 * @property \App\Enums\ProductionRequestStatus $status
 * @property \Illuminate\Support\Carbon $requested_date
 * @property \Illuminate\Support\Carbon|null $completed_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SalesOrder $salesOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRequest whereCompletedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRequest whereRequestNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRequest whereRequestedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRequest whereSalesOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRequest whereUpdatedAt($value)
 */
	class ProductionRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $tembak_code
 * @property string|null $cook_code
 * @property numeric|null $wet_result_weight
 * @property numeric|null $residual_resin_weight
 * @property int|null $residual_resin_material_id
 * @property numeric|null $dried_result_weight
 * @property int|null $output_material_id
 * @property string $status
 * @property \Illuminate\Support\Carbon $tembak_date
 * @property \Illuminate\Support\Carbon|null $report_date
 * @property string $pic_name
 * @property string|null $report_pic_name
 * @property string|null $notes
 * @property string|null $report_notes
 * @property string|null $signature_path
 * @property string|null $report_signature_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductionTembakDryingLog> $dryingLogs
 * @property-read int|null $drying_logs_count
 * @property-read string $cook_label
 * @property-read float $current_drying_weight
 * @property-read int $drying_duration_days
 * @property-read float $shrinkage_percentage
 * @property-read string $status_label
 * @property-read float $total_shrinkage_weight
 * @property-read \App\Models\ProductionTembakDryingLog|null $latestDryingLog
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductionTembakMaterial> $materials
 * @property-read int|null $materials_count
 * @property-read \App\Models\Material|null $outputMaterial
 * @property-read \App\Models\Material|null $residualResinMaterial
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereCookCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereDriedResultWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereOutputMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereReportDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereReportNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereReportPicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereReportSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereResidualResinMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereResidualResinWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereTembakCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereTembakDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakBatch whereWetResultWeight($value)
 */
	class ProductionTembakBatch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $production_tembak_batch_id
 * @property \Illuminate\Support\Carbon $weighed_date
 * @property numeric $previous_weight
 * @property numeric $new_weight
 * @property numeric $shrinkage_weight
 * @property string $pic_name
 * @property string|null $notes
 * @property bool $is_final
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProductionTembakBatch $batch
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog whereIsFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog whereNewWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog wherePreviousWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog whereProductionTembakBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog whereShrinkageWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakDryingLog whereWeighedDate($value)
 */
	class ProductionTembakDryingLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $production_tembak_batch_id
 * @property int $material_id
 * @property string $type
 * @property numeric $weight
 * @property numeric $unit_cost
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProductionTembakBatch $batch
 * @property-read \App\Models\Material $material
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakMaterial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakMaterial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakMaterial query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakMaterial whereMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakMaterial whereProductionTembakBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakMaterial whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakMaterial whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakMaterial whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionTembakMaterial whereWeight($value)
 */
	class ProductionTembakMaterial extends \Eloquent {}
}

namespace App\Models\Production{
/**
 * @property int $id
 * @property string $carve_code
 * @property string $status
 * @property int $source_material_id
 * @property numeric $initial_weight
 * @property numeric|null $dried_weight
 * @property numeric|null $drying_loss_weight
 * @property string $carve_date
 * @property string $pic_name
 * @property string|null $report_date
 * @property string|null $report_pic_name
 * @property string|null $notes
 * @property string|null $signature_path
 * @property string|null $report_signature_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\Production\MaterialCarveBatchFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereCarveCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereCarveDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereDriedWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereDryingLossWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereInitialWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereReportDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereReportPicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereReportSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereSourceMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveBatch whereUpdatedAt($value)
 */
	class MaterialCarveBatch extends \Eloquent {}
}

namespace App\Models\Production{
/**
 * @property int $id
 * @property int $material_carve_batch_id
 * @property int $target_material_id
 * @property numeric $result_weight
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereMaterialCarveBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereResultWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereTargetMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCarveItem whereUpdatedAt($value)
 */
	class MaterialCarveItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $customer_id
 * @property string $order_number
 * @property \App\Enums\SalesOrderStatus $status
 * @property numeric $total_amount
 * @property string|null $pic_name
 * @property string|null $signature
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\ProductionRequest|null $productionRequest
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrder whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrder whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrder wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrder whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrder whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrder whereUpdatedAt($value)
 */
	class SalesOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $sales_order_id
 * @property int $product_id
 * @property float $quantity
 * @property string $unit
 * @property numeric $unit_price
 * @property numeric $subtotal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\SalesOrder $salesOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrderItem whereSalesOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrderItem whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrderItem whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrderItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesOrderItem whereUpdatedAt($value)
 */
	class SalesOrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \App\Enums\UserRole $role
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $manager_name
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseItem> $activeItems
 * @property-read int|null $active_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseLocation> $activeLocations
 * @property-read int|null $active_locations_count
 * @property-read int $total_items
 * @property-read int $total_stock
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseLocation> $locations
 * @property-read int|null $locations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseItem> $lowStockItems
 * @property-read int|null $low_stock_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseStockAdjustment> $stockAdjustments
 * @property-read int|null $stock_adjustments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseStockMovement> $stockMovements
 * @property-read int|null $stock_movements_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch whereManagerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseBranch withoutTrashed()
 */
	class WarehouseBranch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $warehouse_product_id
 * @property string $sku
 * @property string $barcode
 * @property string $qr_code
 * @property string $name
 * @property string|null $production_branch
 * @property string|null $description
 * @property string|null $category
 * @property string $unit
 * @property numeric|null $weight
 * @property string|null $dimensions
 * @property int $min_stock
 * @property int|null $max_stock
 * @property numeric $unit_price
 * @property int $warehouse_branch_id
 * @property int|null $warehouse_location_id
 * @property int $current_stock
 * @property string|null $pic_name
 * @property string|null $signature_path
 * @property string|null $batch_number
 * @property \Illuminate\Support\Carbon|null $expiry_date
 * @property array<array-key, mixed>|null $custom_fields
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\WarehouseBranch|null $branch
 * @property-read string $stock_status
 * @property-read float $stock_value
 * @property-read \App\Models\WarehouseLocation|null $location
 * @property-read \App\Models\WarehouseProduct|null $product
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseStockAdjustment> $stockAdjustments
 * @property-read int|null $stock_adjustments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseStockMovement> $stockMovements
 * @property-read int|null $stock_movements_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereBatchNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereCurrentStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereCustomFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereDimensions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereMaxStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereMinStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereProductionBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereQrCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereWarehouseBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereWarehouseLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereWarehouseProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseItem withoutTrashed()
 */
	class WarehouseItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $warehouse_branch_id
 * @property string $code
 * @property string $name
 * @property string|null $zone
 * @property string|null $rack
 * @property string|null $bin
 * @property int|null $capacity
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\WarehouseBranch|null $branch
 * @property-read int|null $available_capacity
 * @property-read int $current_capacity
 * @property-read string $full_location_code
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseStockMovement> $stockMovements
 * @property-read int|null $stock_movements_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation whereBin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation whereRack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation whereWarehouseBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation whereZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseLocation withoutTrashed()
 */
	class WarehouseLocation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $unit
 * @property numeric $unit_price
 * @property string|null $category
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WarehouseItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseProduct withoutTrashed()
 */
	class WarehouseProduct extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $warehouse_item_id
 * @property int $warehouse_branch_id
 * @property string $reason
 * @property int $quantity_before
 * @property int $quantity_after
 * @property int $adjustment_quantity
 * @property string|null $notes
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $adjustment_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\WarehouseBranch|null $branch
 * @property-read \App\Models\WarehouseItem|null $item
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment whereAdjustmentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment whereAdjustmentQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment whereQuantityAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment whereQuantityBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment whereWarehouseBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockAdjustment whereWarehouseItemId($value)
 */
	class WarehouseStockAdjustment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $warehouse_item_id
 * @property int $warehouse_branch_id
 * @property int|null $warehouse_location_id
 * @property string $type
 * @property int $quantity
 * @property int $stock_before
 * @property int $stock_after
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string|null $notes
 * @property string|null $pic_name
 * @property string|null $signature_path
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $movement_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\WarehouseBranch|null $branch
 * @property-read int $movement_difference
 * @property-read \App\Models\WarehouseItem|null $item
 * @property-read \App\Models\WarehouseLocation|null $location
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereMovementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement wherePicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereStockAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereStockBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereWarehouseBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereWarehouseItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WarehouseStockMovement whereWarehouseLocationId($value)
 */
	class WarehouseStockMovement extends \Eloquent {}
}

