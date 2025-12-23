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
        Schema::table('services', function (Blueprint $table) {
            // Drop the old enum column and recreate it with new values
            DB::statement("ALTER TABLE services MODIFY COLUMN type ENUM('nails_art', 'eyelash', 'other') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Revert back to original enum values
            DB::statement("ALTER TABLE services MODIFY COLUMN type ENUM('nails_art', 'eyelash') NOT NULL");
        });
    }
};
