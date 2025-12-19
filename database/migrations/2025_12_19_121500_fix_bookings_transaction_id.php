<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if transaction_id column exists first
        if (!Schema::hasColumn('bookings', 'transaction_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('transaction_id')->nullable()->after('notes');
            });
        }

        // Update payment_status enum
        try {
            DB::statement("ALTER TABLE bookings MODIFY payment_status ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending'");
        } catch (\Exception $e) {
            // If it fails, the enum might already be correct
        }

        // Drop columns if they exist
        if (Schema::hasColumn('bookings', 'payment_proof_path')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('payment_proof_path');
            });
        }

        if (Schema::hasColumn('bookings', 'payment_verified_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('payment_verified_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'payment_proof_path')) {
                $table->string('payment_proof_path')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('bookings', 'payment_verified_at')) {
                $table->timestamp('payment_verified_at')->nullable();
            }
            if (Schema::hasColumn('bookings', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }
        });
    }
};
