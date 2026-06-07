<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('short_bio');
            $table->string('expertise')->nullable()->after('bio');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->text('sources')->nullable()->after('body');
            $table->timestamp('content_updated_at')->nullable()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->dropColumn(['bio', 'expertise']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['sources', 'content_updated_at']);
        });
    }
};
