<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('university_id')->constrained('universities')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('passport_no', 64);
            $table->string('status')->default('active');
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('stage_index')->default(1);
            $table->boolean('soft_deleted')->default(false);
            $table->timestamps();
            $table->index(['student_id','soft_deleted']);
        });

        Schema::create('application_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('stage_key');
            $table->string('status')->default('draft');
            $table->json('data')->nullable();
            $table->timestamps();
            $table->unique(['application_id','stage_key']);
        });

        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('stage_key');
            $table->string('doc_type');
            $table->string('file_url');
            $table->string('file_type', 16);
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('uploaded');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index(['application_id','stage_key']);
        });

        Schema::create('staff_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('assigned_at');
            $table->timestamps();
            $table->unique(['staff_id','application_id']);
        });

        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('type');
            $table->string('file_url');
            $table->foreignId('issued_by_staff_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('issued_at');
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->boolean('read')->default(false);
            $table->timestamps();
            $table->index(['application_id','created_at']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 8)->default('MYR');
            $table->string('type');
            $table->string('receipt_url')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('letters');
        Schema::dropIfExists('staff_assignments');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('application_stages');
        Schema::dropIfExists('applications');
    }
};

