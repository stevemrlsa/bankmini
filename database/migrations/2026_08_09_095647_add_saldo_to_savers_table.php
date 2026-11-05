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
        Schema::table('savers', function (Blueprint $table) {
            // Menambahkan kolom saldo setelah kolom security_pin
            $table->bigInteger('saldo')->default(0)->after('security_pin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savers', function (Blueprint $table) {
            // Menghapus kolom jika di-rollback
            $table->dropColumn('saldo');
        });
    }
};
