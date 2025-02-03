<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the `question_type` column if it exists
        if (Schema::hasColumn('questions', 'question_type')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('question_type');
            });
        }

        // Add the `type` column if it doesn't exist
        if (!Schema::hasColumn('questions', 'type')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->string('type')->after('exam_id');
            });
        }
    }

    public function down(): void
    {
        // Revert the changes if needed
        if (Schema::hasColumn('questions', 'type')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
}; 