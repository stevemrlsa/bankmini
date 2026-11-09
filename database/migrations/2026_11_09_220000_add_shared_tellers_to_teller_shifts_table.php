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
            if (!Schema::hasColumn('teller_shifts', 'shared_user_ids')) {
                $table->json('shared_user_ids')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('teller_shifts', 'closed_by')) {
                $table->foreignId('closed_by')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teller_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('teller_shifts', 'closed_by')) {
                $table->dropForeign(['closed_by']);
                $table->dropColumn('closed_by');
            }
            if (Schema::hasColumn('teller_shifts', 'shared_user_ids')) {
                $table->dropColumn('shared_user_ids');
            }
        });
    }
};
