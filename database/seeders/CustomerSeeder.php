<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'PT Teknologi Nusa Jaya',
                'email' => 'procurement@nusajaya.co.id',
                'phone' => '021-5551234',
                'address' => 'Jl. Jendral Sudirman No. 45, Jakarta Selatan',
                'is_active' => true,
            ],
            [
                'name' => 'CV Mandiri Sejahtera',
                'email' => 'info@mandirisejahtera.com',
                'phone' => '022-7778899',
                'address' => 'Jl. Asia Afrika No. 102, Bandung',
                'is_active' => true,
            ],
            [
                'name' => 'PT Inovasi Solusi Digital',
                'email' => 'sales@solusidigital.id',
                'phone' => '031-4445566',
                'address' => 'Jl. Pemuda No. 88, Surabaya',
                'is_active' => true,
            ],
            [
                'name' => 'Koperasi Bahari Makmur',
                'email' => 'koperasi@baharimakmur.org',
                'phone' => '024-3332211',
                'address' => 'Jl. Pandanaran No. 12, Semarang',
                'is_active' => false,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(['email' => $customer['email']], $customer);
        }
    }
}
