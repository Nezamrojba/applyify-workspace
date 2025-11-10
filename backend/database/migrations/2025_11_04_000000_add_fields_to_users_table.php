<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'staff', 'student'])->default('student')->after('password');
            $table->string('whatsapp', 32)->nullable()->after('role');
            $table->string('passport_no', 64)->nullable()->unique()->after('whatsapp');
            $table->boolean('is_active')->default(true)->after('passport_no');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'whatsapp', 'passport_no', 'is_active']);
        });
    }
};

