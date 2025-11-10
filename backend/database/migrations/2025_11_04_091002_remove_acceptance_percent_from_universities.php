<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            if (Schema::hasColumn('universities', 'acceptance_percent')) {
                $table->dropColumn('acceptance_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            if (!Schema::hasColumn('universities', 'acceptance_percent')) {
                $table->unsignedTinyInteger('acceptance_percent')->nullable()->after('country');
            }
        });
    }
};
