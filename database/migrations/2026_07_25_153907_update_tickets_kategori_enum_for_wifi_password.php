<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE tickets MODIFY COLUMN kategori ENUM('PSB', 'Gangguan', 'Cabut Modem', 'Lainnya', 'Ganti Password Wifi') NOT NULL DEFAULT 'Gangguan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE tickets MODIFY COLUMN kategori ENUM('PSB', 'Gangguan', 'Cabut Modem', 'Lainnya') NOT NULL DEFAULT 'Gangguan'");
    }
};
