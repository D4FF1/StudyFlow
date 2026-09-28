<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('Todo');
            $table->string('priority')->default('Medium');
            $table->string('difficulty')->default('Medium');
            $table->string('external_subject_id')->nullable();
            $table->string('subject_name')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->unsignedInteger('estimated_minutes')->default(45);
            $table->unsignedInteger('progress')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_overdue')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
