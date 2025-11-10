<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->unsignedInteger('points');
            $table->string('reason');
            $table->timestamp('credited_at');
            $table->boolean('released')->default(false);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->unique(['application_id','reason']);
            $table->index(['staff_id','released']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_points');
    }
};

