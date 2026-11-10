<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->unsignedBigInteger('view_count')->default(0)->after('revision_count');
            $table->text('editor_note')->nullable()->after('rejection_reason');
        });

        Schema::create('site_daily_metrics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->date('metric_date');
            $table->unsignedBigInteger('page_views')->default(0);
            $table->timestamps();

            $table->unique(['site_id', 'metric_date']);
            $table->index(['metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_daily_metrics');

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn(['view_count', 'editor_note']);
        });
    }
};
