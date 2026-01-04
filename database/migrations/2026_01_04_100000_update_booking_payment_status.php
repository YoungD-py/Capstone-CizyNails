<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Expand enum to include all old and new values
        DB::statement("ALTER TABLE bookings MODIFY payment_status ENUM('pending', 'verified', 'rejected', 'paid', 'cancelled', 'unpaid') NOT NULL DEFAULT 'pending'");
        
        // Step 2: Update existing data
        // Convert 'verified' to 'paid'
        DB::table('bookings')
            ->where('payment_status', 'verified')
            ->update(['payment_status' => 'paid']);
        
        // Convert 'rejected' and 'cancelled' to 'unpaid'
        DB::table('bookings')
            ->whereIn('payment_status', ['rejected', 'cancelled'])
            ->update(['payment_status' => 'unpaid']);

        // Step 3: Set enum to final values only
        DB::statement("ALTER TABLE bookings MODIFY payment_status ENUM('pending', 'paid', 'unpaid') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Expand enum first
        DB::statement("ALTER TABLE bookings MODIFY payment_status ENUM('pending', 'paid', 'unpaid', 'verified', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
        
        // Revert data
        DB::table('bookings')
            ->where('payment_status', 'paid')
            ->update(['payment_status' => 'verified']);
        
        DB::table('bookings')
            ->where('payment_status', 'unpaid')
            ->update(['payment_status' => 'rejected']);

        // Set enum back to old values
        DB::statement("ALTER TABLE bookings MODIFY payment_status ENUM('pending', 'verified', 'rejected', 'paid', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
