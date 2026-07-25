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
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE pembayarans MODIFY metode VARCHAR(255) NOT NULL DEFAULT "cash"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE pembayarans MODIFY metode ENUM('cash', 'transfer_bca', 'transfer_bri', 'transfer_mandiri', 'transfer_bni', 'transfer_lain') NOT NULL DEFAULT 'cash'");
    }
};
