<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('restrict');
            $table->string('account_code');
            $table->enum('position', ['Debit', 'Credit']);
            $table->bigInteger('amount');
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE accounting_journals ADD CONSTRAINT check_journal_amount CHECK (amount > 0)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('accounting_journals', function (Blueprint $table) {
                DB::statement('ALTER TABLE accounting_journals DROP CONSTRAINT check_journal_amount');
            });
        }

        Schema::dropIfExists('accounting_journals');
    }
};