<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tiket')->unique();
            $table->enum('kategori', ['PSB', 'Gangguan', 'Cabut Modem', 'Lainnya'])->default('Gangguan');
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->string('nama_pelapor')->nullable();
            $table->string('no_hp')->nullable();
            $table->text('deskripsi_pekerjaan')->nullable();
            $table->dateTime('jadwal_kunjungan')->nullable();
            $table->enum('status', ['Pending', 'Proses', 'Selesai'])->default('Pending');
            $table->text('penggunaan_alat')->nullable();
            $table->unsignedBigInteger('teknisi_id')->nullable();
            $table->timestamps();

            $table->foreign('pelanggan_id')->references('id')->on('pelanggans')->onDelete('set null');
            $table->foreign('teknisi_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
