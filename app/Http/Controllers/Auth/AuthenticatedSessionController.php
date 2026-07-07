<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * AuthenticatedSessionController — Mengelola sesi login dan logout.
 *
 * Login dan logout dicatat ke activity_logs sebagai bagian dari
 * audit trail sistem (Matkul: Isu Sosial & Keprofesian TI).
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi pengguna.
     * Mencatat aksi login ke activity_logs.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Catat login ke audit trail
        try {
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'user_name'   => auth()->user()->name,
                'user_role'   => auth()->user()->role,
                'action'      => 'login',
                'module'      => 'Auth',
                'description' => 'Login berhasil',
                'ip_address'  => $request->ip(),
                'user_agent'  => substr($request->userAgent() ?? '', 0, 500),
                'url'         => $request->fullUrl(),
                'method'      => 'POST',
            ]);
        } catch (\Exception) {
            // Jangan gagalkan login hanya karena logging error
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Hapus sesi pengguna (logout).
     * Mencatat aksi logout ke activity_logs.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Catat logout sebelum sesi dihapus
        try {
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'user_name'   => auth()->user()?->name,
                'user_role'   => auth()->user()?->role,
                'action'      => 'logout',
                'module'      => 'Auth',
                'description' => 'Logout dari sistem',
                'ip_address'  => $request->ip(),
                'user_agent'  => substr($request->userAgent() ?? '', 0, 500),
                'url'         => $request->fullUrl(),
                'method'      => 'POST',
            ]);
        } catch (\Exception) {
            // Jangan gagalkan logout hanya karena logging error
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
