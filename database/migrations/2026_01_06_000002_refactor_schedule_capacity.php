<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Add new generic booked_count column
            if (!Schema::hasColumn('schedules', 'booked_count')) {
                $table->integer('booked_count')->default(0)->after('time_slot');
            }
        });

        // Keep the old columns for now for backward compatibility
        // We'll handle the migration gradually
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'booked_count')) {
                $table->dropColumn('booked_count');
            }
        });
    }
};
