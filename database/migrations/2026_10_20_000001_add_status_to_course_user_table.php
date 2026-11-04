<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('course_user') && !Schema::hasColumn('course_user', 'status')) {
            Schema::table('course_user', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')->after('user_id');
                $table->index(['course_id', 'status']);
            });

            // Pastikan seluruh data pendaftaran lama tetap berstatus approved
            DB::table('course_user')->update(['status' => 'approved']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('course_user') && Schema::hasColumn('course_user', 'status')) {
            Schema::table('course_user', function (Blueprint $table) {
                $table->dropIndex(['course_id', 'status']);
                $table->dropColumn('status');
            });
        }
    }
};
