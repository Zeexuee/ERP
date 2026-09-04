<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login sistem ERP.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            $routeName = $user->role?->defaultRouteName() ?? 'dashboard';

            return redirect()->route($routeName);
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login pengguna.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->forget('url.intended');

            /** @var User $user */
            $user = Auth::user();
            $targetRoute = $user->role?->defaultRouteName() ?? 'dashboard';

            return redirect()->route($targetRoute);
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'Kredensial yang dimasukkan tidak cocok dengan data pengguna kami.',
            ]);
    }

    /**
     * Beralih role / otorisasi instan antar-modul tanpa perlu logout manual.
     */
    public function quickSwitch(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'target_module' => ['required', 'string', 'in:sales,production,stin,crm,ecommerce'],
        ], [
            'email.required' => 'Email otorisasi wajib diisi.',
            'password.required' => 'Password otorisasi wajib diisi.',
            'target_module.required' => 'Modul tujuan tidak valid.',
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (! Auth::validate($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password otorisasi tidak valid.',
            ], 422);
        }

        /** @var User $targetUser */
        $targetUser = User::where('email', $request->email)->first();

        if (! $targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Akun pengguna tidak ditemukan.',
            ], 404);
        }

        if (! $targetUser->canAccessModule($request->target_module)) {
            $moduleName = match ($request->target_module) {
                'sales' => 'Penjualan (Sales)',
                'production' => 'Produksi',
                'stin' => 'Divisi STIN',
                'crm' => 'Manajemen CRM',
                'ecommerce' => 'Manajemen E-Commerce',
                default => 'Modul yang dituju',
            };

            return response()->json([
                'success' => false,
                'message' => "Akun ini valid, namun TIDAK MEMILIKI hak akses ke modul {$moduleName}.",
            ], 403);
        }

        // Login target user & regenerate session
        Auth::login($targetUser, true);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        $targetUrl = match ($request->target_module) {
            'sales' => route('dashboard'),
            'production' => route('production.dashboard'),
            'stin' => route('stin.dashboard'),
            'crm' => route('crm.dashboard'),
            'ecommerce' => route('ecommerce.dashboard'),
            default => route('dashboard'),
        };

        return response()->json([
            'success' => true,
            'message' => 'Otorisasi berhasil. Mengalihkan ke sistem...',
            'user' => [
                'name' => $targetUser->name,
                'role' => $targetUser->role?->label(),
            ],
            'redirect_url' => $targetUrl,
        ]);
    }

    /**
     * Logout sesi pengguna.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
