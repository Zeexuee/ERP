<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CrmController extends Controller
{
    /**
     * Dashboard Divisi Manajemen CRM.
     */
    public function dashboard(): View
    {
        return view('under-development', [
            'module_name' => 'CRM',
            'division_label' => 'Manajemen Hubungan Pelanggan (CRM)',
            'description' => 'Akses modul Manajemen CRM saat ini ditutup sementara karena fitur sedang dalam tahap pengembangan & integrasi sistem.',
        ]);
    }
}
