<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ROLE = 'super_role';
    case SALES = 'sales';
    case PRODUCTION = 'production';
    case STIN = 'stin';
    case ADMIN_CRM = 'admin_crm';
    case ADMIN_ECOMMERCE = 'admin_ecommerce';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ROLE => 'Super Role',
            self::SALES => 'Sales',
            self::PRODUCTION => 'Produksi',
            self::STIN => 'STIN',
            self::ADMIN_CRM => 'Manajemen CRM',
            self::ADMIN_ECOMMERCE => 'Manajemen E-Commerce',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SUPER_ROLE => 'Akses seluruh divisi tanpa batasan',
            self::SALES => 'Divisi Penjualan & Pesanan Pelanggan',
            self::PRODUCTION => 'Divisi Manufaktur & Perakitan',
            self::STIN => 'Divisi Khusus Perusahaan',
            self::ADMIN_CRM => 'Manajemen Hubungan Pelanggan & Prospek',
            self::ADMIN_ECOMMERCE => 'Manajemen Kanal Penjualan Digital & Marketplace',
        };
    }

    public function defaultRouteName(): string
    {
        return match ($this) {
            self::SUPER_ROLE => 'dashboard',
            self::SALES => 'dashboard',
            self::PRODUCTION => 'production.dashboard',
            self::STIN => 'stin.dashboard',
            self::ADMIN_CRM => 'crm.dashboard',
            self::ADMIN_ECOMMERCE => 'ecommerce.dashboard',
        };
    }

    public function moduleKey(): string
    {
        return match ($this) {
            self::SUPER_ROLE => 'super',
            self::SALES => 'sales',
            self::PRODUCTION => 'production',
            self::STIN => 'stin',
            self::ADMIN_CRM => 'crm',
            self::ADMIN_ECOMMERCE => 'ecommerce',
        };
    }
}
