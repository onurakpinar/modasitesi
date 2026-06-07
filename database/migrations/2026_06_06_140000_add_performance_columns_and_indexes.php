<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedSmallInteger('cover_image_width')->nullable()->after('cover_image_fallback');
            $table->unsignedSmallInteger('cover_image_height')->nullable()->after('cover_image_width');
            $table->index(['status', 'published_at', 'is_featured'], 'posts_public_featured_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'categories_nav_index');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->index('read_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_public_featured_index');
            $table->dropColumn(['cover_image_width', 'cover_image_height']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_nav_index');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropIndex(['read_at']);
            $table->dropIndex(['created_at']);
        });
    }
};
