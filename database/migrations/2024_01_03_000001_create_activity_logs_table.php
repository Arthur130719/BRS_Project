<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel activity_logs untuk mencatat semua aksi pengguna.
     * Mendukung audit trail dan akuntabilitas profesional (Isu Sosial & Keprofesian TI).
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();        // snapshot nama user
            $table->string('user_role', 20)->nullable();   // snapshot role user
            $table->string('action', 100);                 // create, update, delete, login, logout, suspend, aktifkan
            $table->string('module', 100);                 // pelanggan, invoice, pembayaran, dll
            $table->string('description')->nullable();     // deskripsi detail aksi
            $table->string('model_type')->nullable();      // App\Models\Pelanggan
            $table->unsignedBigInteger('model_id')->nullable(); // ID record yang diubah
            $table->json('old_values')->nullable();        // nilai sebelum diubah
            $table->json('new_values')->nullable();        // nilai setelah diubah
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();      // GET, POST, PUT, DELETE
            $table->timestamps();

            // Index untuk query performa tinggi
            $table->index(['user_id', 'created_at']);
            $table->index(['module', 'action']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
