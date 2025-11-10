<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            if (!Schema::hasColumn('universities', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('logo_url');
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('duration_months');
            }
            if (!Schema::hasColumn('courses', 'details')) {
                $table->text('details')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('courses', 'total_tuition_fees')) {
                $table->decimal('total_tuition_fees', 12, 2)->nullable()->after('details');
            }
            if (!Schema::hasColumn('courses', 'procedure_fees')) {
                $table->decimal('procedure_fees', 12, 2)->nullable()->after('total_tuition_fees');
            }
            if (!Schema::hasColumn('courses', 'payment_method')) {
                $table->string('payment_method', 64)->nullable()->after('procedure_fees');
            }
            if (!Schema::hasColumn('courses', 'allow_installments')) {
                $table->boolean('allow_installments')->default(false)->after('payment_method');
            }
            if (!Schema::hasColumn('courses', 'total_years')) {
                $table->unsignedTinyInteger('total_years')->nullable()->after('allow_installments');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'total_years')) $table->dropColumn('total_years');
            if (Schema::hasColumn('courses', 'allow_installments')) $table->dropColumn('allow_installments');
            if (Schema::hasColumn('courses', 'payment_method')) $table->dropColumn('payment_method');
            if (Schema::hasColumn('courses', 'procedure_fees')) $table->dropColumn('procedure_fees');
            if (Schema::hasColumn('courses', 'total_tuition_fees')) $table->dropColumn('total_tuition_fees');
            if (Schema::hasColumn('courses', 'details')) $table->dropColumn('details');
            if (Schema::hasColumn('courses', 'is_active')) $table->dropColumn('is_active');
        });
        Schema::table('universities', function (Blueprint $table) {
            if (Schema::hasColumn('universities', 'is_active')) $table->dropColumn('is_active');
        });
    }
};

