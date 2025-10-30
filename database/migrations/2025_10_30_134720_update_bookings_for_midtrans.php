<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('payment_status');
        });

        DB::statement("ALTER TABLE bookings MODIFY payment_status ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_proof_path', 'payment_verified_at']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_proof_path')->nullable()->after('notes');
            $table->timestamp('payment_verified_at')->nullable();
            $table->dropColumn('transaction_id');
        });

        DB::statement("ALTER TABLE bookings MODIFY payment_status ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
