<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users'); // kasir/admin yg input
            $table->bigInteger('nominal');
            $table->enum('metode', ['cash', 'transfer_bca', 'transfer_bri', 'transfer_mandiri', 'transfer_bni', 'transfer_lain'])->default('cash');
            $table->string('nama_bank')->nullable(); // untuk transfer_lain
            $table->date('tgl_bayar');
            $table->string('keterangan')->nullable();
            $table->string('bukti_transfer')->nullable(); // file path
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
