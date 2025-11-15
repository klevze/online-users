<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIpHashToUserActivitiesTable extends Migration
{
    public function up(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('user_activities', 'user_ip_hash')) {
                $table->string('user_ip_hash')->nullable()->after('user_ip');
                $table->index('user_ip_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            if (Schema::hasColumn('user_activities', 'user_ip_hash')) {
                $table->dropIndex(['user_ip_hash']);
                $table->dropColumn('user_ip_hash');
            }
        });
    }
}
