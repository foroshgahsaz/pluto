<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['site_id', 'parent_id', 'name']);
            $table->index(['site_id', 'parent_id']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->foreignId('folder_id')->nullable()->after('site_id')->constrained('media_folders')->nullOnDelete();
            $table->string('category', 50)->nullable()->after('visibility');
            $table->string('alt_text')->nullable()->after('category');
            $table->index(['site_id', 'folder_id']);
            $table->index(['site_id', 'category']);
            $table->index(['site_id', 'original_name']);
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropConstrainedForeignId('folder_id');
            $table->dropColumn(['category', 'alt_text']);
        });

        Schema::dropIfExists('media_folders');
    }
};
