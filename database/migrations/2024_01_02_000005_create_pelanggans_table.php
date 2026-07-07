<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->nullable()->constrained('pakets')->nullOnDelete();
            $table->foreignId('nas_id')->nullable()->constrained('nas')->nullOnDelete();
            $table->foreignId('olt_id')->nullable()->constrained('olts')->nullOnDelete();
            $table->string('username_pppoe')->unique();
            $table->string('password_pppoe');
            $table->string('nama');
            $table->string('phone', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('ip_pool')->nullable();
            $table->enum('status', ['active', 'suspend', 'inactive'])->default('active');
            $table->string('isolir_by')->nullable(); // 'auto' or 'manual:user_id'
            $table->timestamp('isolir_at')->nullable();
            $table->date('tgl_aktif')->nullable();
            $table->date('expiry')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggans');
    }
};
