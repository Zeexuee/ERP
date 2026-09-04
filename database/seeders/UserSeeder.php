<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds for all system roles.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Sales Officer',
                'email' => 'sales@erp.com',
                'role' => UserRole::SALES,
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Kepala Pabrik & Produksi',
                'email' => 'produksi@erp.com',
                'role' => UserRole::PRODUCTION,
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Petugas Khusus STIN',
                'email' => 'stin@erp.com',
                'role' => UserRole::STIN,
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Admin Manajemen CRM',
                'email' => 'crm@erp.com',
                'role' => UserRole::ADMIN_CRM,
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Admin Manajemen E-Commerce',
                'email' => 'ecommerce@erp.com',
                'role' => UserRole::ADMIN_ECOMMERCE,
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@erp.com',
                'role' => UserRole::SUPER_ROLE,
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
