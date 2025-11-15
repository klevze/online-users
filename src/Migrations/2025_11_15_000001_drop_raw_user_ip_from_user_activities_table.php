<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropRawUserIpFromUserActivitiesTable extends Migration
{
    public function up(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            if (Schema::hasColumn('user_activities', 'user_ip')) {
                // drop index on user_ip if it exists
                try {
                    $table->dropIndex(['user_ip']);
                } catch (\Exception $e) {
                    // ignore if index doesn't exist
                }

                $table->dropColumn('user_ip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            if (! Schema::hasColumn('user_activities', 'user_ip')) {
                $table->string('user_ip')->nullable()->after('id');
                $table->index('user_ip');
            }
        });
    }
}
