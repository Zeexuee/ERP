<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class EcommerceController extends Controller
{
    /**
     * Dashboard Divisi Manajemen E-Commerce.
     */
    public function dashboard(): View
    {
        return view('under-development', [
            'module_name' => 'E-Commerce',
            'division_label' => 'Manajemen Kanal Digital & E-Commerce',
            'description' => 'Akses modul Manajemen E-Commerce saat ini ditutup sementara karena fitur sedang dalam tahap pengembangan & integrasi sistem.',
        ]);
    }
}
