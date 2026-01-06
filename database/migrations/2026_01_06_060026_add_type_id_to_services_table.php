<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('type_id')->nullable()->constrained('types')->onDelete('set null');
            $table->foreignId('subtype_id')->nullable()->constrained('subtypes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Type::class);
            $table->dropForeignIdFor(\App\Models\Subtype::class);
            $table->dropColumn(['type_id', 'subtype_id']);
        });
    }
};
