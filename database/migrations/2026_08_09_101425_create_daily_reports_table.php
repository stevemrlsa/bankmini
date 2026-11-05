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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teller_id')->constrained('users')->onDelete('restrict');

            $table->date('report_date');

            $table->bigInteger('opening_balance')->default(0);
            $table->bigInteger('total_deposits')->default(0);
            $table->bigInteger('total_withdrawals')->default(0);

            $table->bigInteger('closing_balance_system');
            $table->bigInteger('closing_balance_physical');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('approval_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
