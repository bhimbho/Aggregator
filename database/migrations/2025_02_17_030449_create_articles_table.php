<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type')->index();
            $table->string('source')->nullable()->index();
            $table->longText('author')->nullable();
            $table->mediumText('title');
            $table->mediumText('description')->nullable();
            $table->mediumText('url');
            $table->mediumText('urlToImage')->nullable();
            $table->longText('content')->nullable();
            $table->string('category')->nullable()->index();
            $table->dateTime('publishedAt')->index();
            $table->string('platform')->index();
            $table->timestamps();

            $table->index(['platform', 'category', 'publishedAt'], 'idx_platform_category_published');
            $table->index(['source', 'publishedAt'], 'idx_source_published');
            $table->fullText(['title', 'description', 'content'], 'idx_fulltext_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
