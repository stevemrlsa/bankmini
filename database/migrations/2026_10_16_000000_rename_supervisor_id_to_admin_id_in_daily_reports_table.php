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
        // 1. Rename column supervisor_id to admin_id in daily_reports if exists
        if (Schema::hasColumn('daily_reports', 'supervisor_id')) {
            Schema::table('daily_reports', function (Blueprint $table) {
                $table->renameColumn('supervisor_id', 'admin_id');
            });
        }

        // 2. Update existing users with role 'Supervisor' to 'Administrator'
        if (Schema::hasTable('users')) {
            DB::table('users')->where('role', 'Supervisor')->update([
                'role' => 'Administrator'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('daily_reports', 'admin_id')) {
            Schema::table('daily_reports', function (Blueprint $table) {
                $table->renameColumn('admin_id', 'supervisor_id');
            });
        }
    }
};
