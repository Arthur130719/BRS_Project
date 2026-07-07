<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->string('no_invoice')->unique(); // INV-2025-XXXX
            $table->string('periode'); // "Juni 2025"
            $table->bigInteger('nominal');
            $table->enum('status', ['unpaid', 'paid', 'partial'])->default('unpaid');
            $table->date('tgl_jatuh_tempo');
            $table->date('tgl_bayar')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
