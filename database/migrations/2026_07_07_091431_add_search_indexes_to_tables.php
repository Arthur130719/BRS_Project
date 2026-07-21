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
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->index('nama');
            $table->index('username_pppoe');
            $table->index('ip_address');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('no_invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropIndex(['nama']);
            $table->dropIndex(['username_pppoe']);
            $table->dropIndex(['ip_address']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['no_invoice']);
        });
    }
};
