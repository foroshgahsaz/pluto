<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backup_records', function (Blueprint $table) {
            $table->string('type', 20)->default('full')->after('status');
            $table->index(['type', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('backup_records', function (Blueprint $table) {
            $table->dropIndex(['type', 'status', 'created_at']);
            $table->dropColumn('type');
        });
    }
};
