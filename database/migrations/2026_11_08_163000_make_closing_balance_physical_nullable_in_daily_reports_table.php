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
        if (Schema::hasTable('daily_reports') && Schema::hasColumn('daily_reports', 'closing_balance_physical')) {
            try {
                Schema::table('daily_reports', function (Blueprint $table) {
                    $table->bigInteger('closing_balance_physical')->nullable()->change();
                });
            } catch (\Throwable $e) {
                // Fallback raw statement for MySQL if change() driver differs
                try {
                    DB::statement("ALTER TABLE daily_reports MODIFY closing_balance_physical BIGINT NULL");
                } catch (\Throwable $ex) {}
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('daily_reports') && Schema::hasColumn('daily_reports', 'closing_balance_physical')) {
            try {
                Schema::table('daily_reports', function (Blueprint $table) {
                    $table->bigInteger('closing_balance_physical')->default(0)->change();
                });
            } catch (\Throwable $e) {
                try {
                    DB::statement("ALTER TABLE daily_reports MODIFY closing_balance_physical BIGINT NOT NULL DEFAULT 0");
                } catch (\Throwable $ex) {}
            }
        }
    }
};
