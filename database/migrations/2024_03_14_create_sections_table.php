<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('position')->default(0);
            $table->decimal('total_points', 8, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Update questions table to add section_id
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('exam_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeignId('section_id');
        });
        Schema::dropIfExists('sections');
    }
}; 