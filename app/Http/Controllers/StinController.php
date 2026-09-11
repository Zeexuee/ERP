<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class StinController extends Controller
{
    /**
     * Dashboard Khusus Divisi STIN.
     */
    public function dashboard(): View
    {
        return view('under-development', [
            'module_name' => 'STIN',
            'division_label' => 'Divisi Khusus STIN',
            'description' => 'Akses modul Divisi STIN saat ini ditutup sementara karena fitur sedang dalam tahap pengembangan & integrasi sistem.',
        ]);
    }
}
