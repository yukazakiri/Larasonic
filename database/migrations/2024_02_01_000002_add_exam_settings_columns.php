<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->integer('time_limit')->default(0)->after('description');
            $table->integer('passing_score')->default(60)->after('time_limit');
            $table->integer('attempts_limit')->default(0)->after('passing_score');
            $table->boolean('show_correct_answers')->default(true)->after('attempts_limit');
            $table->boolean('is_public')->default(false)->after('show_correct_answers');
            $table->dateTime('available_from')->nullable()->after('is_public');
            $table->dateTime('available_to')->nullable()->after('available_from');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'time_limit',
                'passing_score',
                'attempts_limit',
                'show_correct_answers',
                'is_public',
                'available_from',
                'available_to'
            ]);
        });
    }
}; 