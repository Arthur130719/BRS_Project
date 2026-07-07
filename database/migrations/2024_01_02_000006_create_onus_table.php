<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olts')->cascadeOnDelete();
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggans')->nullOnDelete();
            $table->string('serial_number')->unique();
            $table->string('port')->nullable(); // e.g. 0/1/1
            $table->decimal('rx_power', 6, 2)->nullable(); // dBm
            $table->decimal('tx_power', 6, 2)->nullable(); // dBm
            $table->enum('status', ['online', 'offline', 'weak'])->default('offline');
            $table->string('model')->nullable(); // ZTE F660, F670L
            $table->string('uptime')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onus');
    }
};
