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
                'name' => 'PT Oud Nusantara Ekspor',
                'email' => 'procurement@oudnusantara.com',
                'phone' => '031-8492011',
                'address' => 'Kawasan Pergudangan Berlian Blok C-12, Tanjung Perak, Surabaya',
                'is_active' => true,
            ],
            [
                'name' => 'Toko Parfum & Herbal Al-Madinah',
                'email' => 'almadinah.gaharu@gmail.com',
                'phone' => '0812-8899-7711',
                'address' => 'Pusat Grosir Tanah Abang Blok B Lt. 3 No. 18, Jakarta Pusat',
                'is_active' => true,
            ],
            [
                'name' => 'Yayasan Majelis & Pesantren Darussalam',
                'email' => 'pengadaan@darussalam-bogor.id',
                'phone' => '0813-2233-4455',
                'address' => 'Jl. Raya Puncak Km. 72, Tugu Selatan, Cisarua, Bogor',
                'is_active' => true,
            ],
            [
                'name' => 'Arabian Aroma & Bukhoor Collection',
                'email' => 'order@arabianaroma.co.id',
                'phone' => '022-7201992',
                'address' => 'Jl. Braga No. 89, Sumur Bandung, Kota Bandung',
                'is_active' => true,
            ],
            [
                'name' => 'Butik Gaharu Al-Barakah',
                'email' => 'albarakah.gaharu@gmail.com',
                'phone' => '0811-6655-4433',
                'address' => 'Jl. Gatot Subroto No. 120, Medan Petisah, Medan',
                'is_active' => false,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(['email' => $customer['email']], $customer);
        }
    }
}
