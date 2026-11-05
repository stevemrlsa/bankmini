<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savers', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique();
            $table->string('student_id')->unique();
            $table->string('student_name');
            $table->string('grade_class');
            $table->string('security_pin');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savers');
    }


};