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
        Schema::table('teller_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('teller_shifts', 'difference')) {
                $table->bigInteger('difference')->nullable()->after('system_balance');
            }
            if (!Schema::hasColumn('teller_shifts', 'notes')) {
                $table->text('notes')->nullable()->after('difference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teller_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('teller_shifts', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('teller_shifts', 'difference')) {
                $table->dropColumn('difference');
            }
        });
    }
};

