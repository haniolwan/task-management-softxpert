<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks_dependencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->constrained('tasks')->onDelete('cascade');
            $table->unsignedBigInteger('depends_on_task_id')->constrained('tasks')->onDelete('cascade');
            $table->unique(['task_id', 'depends_on_task_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks_dependencies');
    }
};
