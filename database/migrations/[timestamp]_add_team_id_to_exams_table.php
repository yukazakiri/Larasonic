<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add nullable team_id column
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('team_id')
                  ->after('id')
                  ->nullable()
                  ->constrained();
        });

        // Step 2: Update existing records to use the first team
        if ($firstTeam = \App\Models\Team::first()) {
            DB::table('exams')->whereNull('team_id')->update([
                'team_id' => $firstTeam->id
            ]);
        }

        // Step 3: Make team_id non-nullable
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
        });
    }
}; 