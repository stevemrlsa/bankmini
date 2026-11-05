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
            // 1. Make account_number nullable (generated during first deposit)
            $table->string('account_number')->nullable()->change();

            // 2. Add gender column if not exists
            if (!Schema::hasColumn('savers', 'gender')) {
                $table->string('gender', 20)->nullable()->after('grade_class');
            }

            // 3. Add password column for saver portal if not exists
            if (!Schema::hasColumn('savers', 'password')) {
                $table->string('password')->nullable()->after('security_pin');
            }

            // 4. Add status column with default 'inactive'
            if (!Schema::hasColumn('savers', 'status')) {
                $table->string('status', 20)->default('inactive')->after('saldo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savers', function (Blueprint $table) {
            $table->string('account_number')->nullable(false)->change();

            if (Schema::hasColumn('savers', 'gender')) {
                $table->dropColumn('gender');
            }
            if (Schema::hasColumn('savers', 'password')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn('savers', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
