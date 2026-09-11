<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('db:clean-except-users {--force : Lewati konfirmasi interaktif}')]
#[Description('Menghapus seluruh data seeder dan transaksi di database kecuali data akun user')]
class ResetDatabaseData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('Perhatian: Perintah ini akan menghapus seluruh data di database (Katalog, Pelanggan, Transaksi Sales, Produksi, Gudang, Log) KECUALI data Users.');

        if (! $this->option('force') && ! $this->confirm('Apakah Anda yakin ingin melanjutkan penghapusan data?', true)) {
            $this->info('Operasi dibatalkan.');

            return self::SUCCESS;
        }

        $this->info('Memulai pembersihan database...');

        Schema::disableForeignKeyConstraints();

        $tables = [
            // 1. Modul Produksi & Laporan Batch
            'production_daily_logs',
            'production_batch_materials',
            'production_batches',

            // 2. Modul Gudang & Log Mutasi Bahan Baku
            'material_logs',
            'material_receipts',
            'materials',

            // 3. Modul Finansial, Pembayaran & Penjualan
            'payments',
            'invoices',
            'production_requests',
            'sales_order_items',
            'sales_orders',

            // 4. Modul Produk, Branch & Master Pelanggan
            'product_branches',
            'products',
            'customers',
        ];

        $clearedCount = 0;
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $clearedCount++;
            }
        }

        Schema::enableForeignKeyConstraints();

        $userCount = User::count();

        $this->newLine();
        $this->info("✓ Berhasil membersihkan {$clearedCount} tabel data (Transaksi, Katalog, Pelanggan, Gudang, Produksi, Log).");
        $this->info("✓ Data akun User TIDAK DIHAPUS dan tetap aman ({$userCount} akun pengguna aktif).");
        $this->newLine();

        return self::SUCCESS;
    }
}
