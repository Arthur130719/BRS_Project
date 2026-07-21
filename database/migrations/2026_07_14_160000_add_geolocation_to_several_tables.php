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
            $table->decimal('latitude', 10, 8)->nullable()->after('alamat');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });

        Schema::table('permohonans', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('alamat');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('alamat');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('alamat');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
