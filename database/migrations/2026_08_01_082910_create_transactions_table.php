<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; 

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('saver_id')->nullable()->constrained('savers')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            
            $table->dateTime('transaction_date');
            $table->enum('transaction_type', ['Deposit', 'Withdrawal', 'Vault_Transfer']); 
            $table->bigInteger('amount'); 
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE transactions ADD CONSTRAINT check_positive_amount CHECK (amount > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};