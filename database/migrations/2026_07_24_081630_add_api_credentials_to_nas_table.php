<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nas', function (Blueprint $table) {
            $table->string('api_user')->nullable()->after('ip_address');
            $table->string('api_password')->nullable()->after('api_user');
            $table->integer('api_port')->default(8728)->after('api_password');
        });
    }

    public function down(): void
    {
        Schema::table('nas', function (Blueprint $table) {
            $table->dropColumn(['api_user', 'api_password', 'api_port']);
        });
    }
};
