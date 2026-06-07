<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_briefs', function (Blueprint $table) {
            $table->id();
            $table->string('title_suggestion');
            $table->string('topic_category');
            $table->string('target_audience');
            $table->string('search_intent');
            $table->text('content_summary');
            $table->text('subheadings');
            $table->text('suggested_internal_links')->nullable();
            $table->text('cover_image_note')->nullable();
            $table->date('planned_publish_date')->nullable();
            $table->string('status')->default('idea');
            $table->foreignId('assigned_editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('topic_category');
            $table->index('planned_publish_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_briefs');
    }
};
