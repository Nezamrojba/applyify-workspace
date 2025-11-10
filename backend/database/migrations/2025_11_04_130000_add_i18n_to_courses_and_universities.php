<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'i18n')) $table->json('i18n')->nullable()->after('duration_months');
        });
        Schema::table('universities', function (Blueprint $table) {
            if (!Schema::hasColumn('universities', 'i18n')) $table->json('i18n')->nullable()->after('logo_url');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'i18n')) $table->dropColumn('i18n');
        });
        Schema::table('universities', function (Blueprint $table) {
            if (Schema::hasColumn('universities', 'i18n')) $table->dropColumn('i18n');
        });
    }
};

