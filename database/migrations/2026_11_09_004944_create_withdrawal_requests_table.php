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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('saver_id')->constrained('savers')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('scheduled_date'); // Tanggal janji penarikan dana besar (minimal H-1)
            $table->text('purpose')->nullable(); // Keperluan / tujuan penarikan
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('pending'); // pending, approved_teller, approved, rejected, completed, cancelled
            
            // Persetujuan Teller & Admin
            $table->foreignId('teller_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('teller_approved_at')->nullable();
            $table->foreignId('admin_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('admin_approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Eksekusi Pencairan Dana
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disbursed_at')->nullable();

            // Petugas yang membantu membuat pengajuan (jika diajukan via teller/admin)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
