<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('isolir_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->enum('aksi', ['isolir', 'aktifkan']); // isolir or aktifkan
            $table->enum('metode', ['auto', 'manual'])->default('manual');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // null if auto
            $table->string('alasan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isolir_logs');
    }
};
