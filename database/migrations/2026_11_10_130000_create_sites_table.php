<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('type', 20);
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->string('domain')->nullable();
            $table->string('locale', 10)->default('fa');
            $table->string('timezone', 64)->default('Asia/Tehran');
            $table->boolean('is_active')->default(true);
            $table->json('settings_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
