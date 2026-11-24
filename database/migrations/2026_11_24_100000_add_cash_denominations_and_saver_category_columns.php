<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('daily_reports') && !Schema::hasColumn('daily_reports', 'cash_denominations')) {
            Schema::table('daily_reports', function (Blueprint $table) {
                $table->json('cash_denominations')->nullable()->after('closing_balance_physical');
            });
        }

        if (Schema::hasTable('teller_shifts') && !Schema::hasColumn('teller_shifts', 'cash_denominations')) {
            Schema::table('teller_shifts', function (Blueprint $table) {
                $table->json('cash_denominations')->nullable()->after('closing_balance');
            });
        }

        if (Schema::hasTable('savers') && !Schema::hasColumn('savers', 'category')) {
            Schema::table('savers', function (Blueprint $table) {
                $table->string('category', 40)->default('student')->after('grade_class');
            });
        }

        if (Schema::hasTable('members') && !Schema::hasColumn('members', 'category')) {
            Schema::table('members', function (Blueprint $table) {
                $table->string('category', 40)->default('student')->after('grade_class');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('daily_reports') && Schema::hasColumn('daily_reports', 'cash_denominations')) {
            Schema::table('daily_reports', function (Blueprint $table) {
                $table->dropColumn('cash_denominations');
            });
        }

        if (Schema::hasTable('teller_shifts') && Schema::hasColumn('teller_shifts', 'cash_denominations')) {
            Schema::table('teller_shifts', function (Blueprint $table) {
                $table->dropColumn('cash_denominations');
            });
        }

        if (Schema::hasTable('savers') && Schema::hasColumn('savers', 'category')) {
            Schema::table('savers', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }

        if (Schema::hasTable('members') && Schema::hasColumn('members', 'category')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};
