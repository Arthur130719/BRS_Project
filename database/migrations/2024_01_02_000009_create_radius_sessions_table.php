<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radius_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggans')->nullOnDelete();
            $table->string('username');
            $table->string('ip_address', 45)->nullable();
            $table->string('nas_id')->nullable();
            $table->string('uptime')->nullable();
            $table->bigInteger('dl_bytes')->default(0);
            $table->bigInteger('ul_bytes')->default(0);
            $table->string('rate')->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radius_sessions');
    }
};
