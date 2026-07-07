<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\IsolirLog;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * NetCORE Feature Tests
 *
 * Unit test untuk memverifikasi logika bisnis utama sistem:
 * 1. Auto-isolir otomatis berdasarkan invoice overdue
 * 2. Pembuatan invoice dan validasinya
 * 3. Middleware role memblokir akses yang tidak berwenang
 *
 * Mendukung mata kuliah Pengembangan Perangkat Lunak —
 * membuktikan penerapan software testing dalam SDLC.
 */
class NetCoreFeatureTest extends TestCase
{
    use RefreshDatabase;

    // ── Helper: buat user dengan role tertentu ──────────────────────────────

    private function makeUser(string $role = 'admin'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function makePelanggan(string $status = 'active'): Pelanggan
    {
        $paket = Paket::create([
            'nama'        => 'Paket Test 10Mbps',
            'harga'       => 150000,
            'speed_up'    => '10M',
            'speed_down'  => '10M',
            'is_active'   => true,
        ]);

        return Pelanggan::create([
            'username_pppoe' => 'testuser01',
            'password_pppoe' => 'secret123',
            'nama'           => 'Pelanggan Test',
            'paket_id'       => $paket->id,
            'status'         => $status,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // TEST 1 — Auto-Isolir Logic
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function auto_isolir_menonaktifkan_pelanggan_dengan_invoice_overdue(): void
    {
        // Siapkan konfigurasi
        SystemSetting::set('auto_isolir_enabled', '1');
        SystemSetting::set('grace_period_days', '0');

        // Buat pelanggan aktif dengan invoice overdue
        $pelanggan = $this->makePelanggan('active');
        Invoice::create([
            'pelanggan_id'    => $pelanggan->id,
            'no_invoice'      => 'INV-TEST-001',
            'periode'         => 'Juni 2025',
            'nominal'         => 150000,
            'status'          => 'unpaid',
            'tgl_jatuh_tempo' => Carbon::yesterday(),
        ]);

        // Jalankan command
        $this->artisan('netcore:auto-isolir')->assertSuccessful();

        // Pelanggan harus berstatus suspend
        $this->assertDatabaseHas('pelanggans', [
            'id'     => $pelanggan->id,
            'status' => 'suspend',
        ]);

        // Harus ada log isolir
        $this->assertDatabaseHas('isolir_logs', [
            'pelanggan_id' => $pelanggan->id,
            'aksi'         => 'isolir',
            'metode'       => 'auto',
        ]);

        // Harus ada activity log
        $this->assertDatabaseHas('activity_logs', [
            'action'     => 'suspend',
            'module'     => 'Pelanggan',
            'model_id'   => $pelanggan->id,
        ]);
    }

    /** @test */
    public function auto_isolir_tidak_berjalan_jika_dinonaktifkan(): void
    {
        SystemSetting::set('auto_isolir_enabled', '0');

        $pelanggan = $this->makePelanggan('active');
        Invoice::create([
            'pelanggan_id'    => $pelanggan->id,
            'no_invoice'      => 'INV-TEST-002',
            'periode'         => 'Juni 2025',
            'nominal'         => 150000,
            'status'          => 'unpaid',
            'tgl_jatuh_tempo' => Carbon::yesterday(),
        ]);

        $this->artisan('netcore:auto-isolir')->assertSuccessful();

        // Pelanggan TIDAK boleh berubah status
        $this->assertDatabaseHas('pelanggans', [
            'id'     => $pelanggan->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function auto_isolir_tidak_isolir_jika_masih_dalam_grace_period(): void
    {
        SystemSetting::set('auto_isolir_enabled', '1');
        SystemSetting::set('grace_period_days', '7'); // grace period 7 hari

        $pelanggan = $this->makePelanggan('active');
        Invoice::create([
            'pelanggan_id'    => $pelanggan->id,
            'no_invoice'      => 'INV-TEST-003',
            'periode'         => 'Juni 2025',
            'nominal'         => 150000,
            'status'          => 'unpaid',
            'tgl_jatuh_tempo' => Carbon::now()->subDays(3), // baru 3 hari lewat, grace 7
        ]);

        $this->artisan('netcore:auto-isolir')->assertSuccessful();

        // Masih dalam grace period, tidak boleh di-isolir
        $this->assertDatabaseHas('pelanggans', [
            'id'     => $pelanggan->id,
            'status' => 'active',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // TEST 2 — Role Middleware
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function teknisi_tidak_bisa_akses_halaman_invoice(): void
    {
        $teknisi = $this->makeUser('teknisi');

        $response = $this->actingAs($teknisi)->get('/invoice');

        // Harus di-redirect atau forbidden
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_bisa_akses_semua_halaman(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($admin)->get('/pelanggan')->assertStatus(200);
        $this->actingAs($admin)->get('/pengaturan')->assertStatus(200);
    }

    /** @test */
    public function kasir_bisa_akses_invoice_tapi_tidak_bisa_akses_pengaturan(): void
    {
        $kasir = $this->makeUser('kasir');

        $this->actingAs($kasir)->get('/invoice')->assertStatus(200);
        $this->actingAs($kasir)->get('/pengaturan')->assertStatus(403);
    }

    /** @test */
    public function guest_diredirect_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/pelanggan')->assertRedirect('/login');
    }

    // ══════════════════════════════════════════════════════════════════════
    // TEST 3 — Invoice Validation
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function admin_bisa_buat_invoice_dengan_data_valid(): void
    {
        $admin     = $this->makeUser('admin');
        $pelanggan = $this->makePelanggan();

        $response = $this->actingAs($admin)->post('/invoice', [
            'pelanggan_id'    => $pelanggan->id,
            'periode'         => 'Juli 2025',
            'nominal'         => 150000,
            'tgl_jatuh_tempo' => Carbon::now()->addDays(30)->format('Y-m-d'),
            'status'          => 'unpaid',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'pelanggan_id' => $pelanggan->id,
            'periode'      => 'Juli 2025',
            'nominal'      => 150000,
            'status'       => 'unpaid',
        ]);
    }

    /** @test */
    public function invoice_gagal_dibuat_tanpa_nominal(): void
    {
        $admin     = $this->makeUser('admin');
        $pelanggan = $this->makePelanggan();

        $response = $this->actingAs($admin)->post('/invoice', [
            'pelanggan_id'    => $pelanggan->id,
            'periode'         => 'Juli 2025',
            'tgl_jatuh_tempo' => Carbon::now()->addDays(30)->format('Y-m-d'),
            // nominal sengaja tidak diisi
        ]);

        $response->assertSessionHasErrors(['nominal']);
    }

    // ══════════════════════════════════════════════════════════════════════
    // TEST 4 — Activity Log
    // ══════════════════════════════════════════════════════════════════════

    /** @test */
    public function activity_log_tercatat_saat_pelanggan_di_suspend(): void
    {
        $admin     = $this->makeUser('admin');
        $pelanggan = $this->makePelanggan('active');

        $this->actingAs($admin)->post("/pelanggan/{$pelanggan->id}/suspend");

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'suspend',
            'module' => 'Pelanggan',
            'user_id' => $admin->id,
        ]);
    }
}
