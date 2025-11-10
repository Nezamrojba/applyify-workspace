<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('program_code')->nullable();
            $table->string('intake')->nullable();
            $table->decimal('tuition_fee_per_credit_hour', 10, 2)->default(0);
            $table->json('semesters')->nullable();
            $table->json('one_time_fees')->nullable();
            $table->json('discounts')->nullable();
            $table->json('payment_methods')->nullable();
            $table->json('policies')->nullable();
            $table->json('i18n')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_fee_structures');
    }
};
