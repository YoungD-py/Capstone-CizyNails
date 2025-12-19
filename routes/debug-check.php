<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Route::get('/debug/check-columns', function() {
    try {
        $columns = DB::select("SHOW COLUMNS FROM bookings");
        $columnNames = collect($columns)->pluck('Field')->toArray();
        
        return response()->json([
            'columns' => $columnNames,
            'has_transaction_id' => in_array('transaction_id', $columnNames),
            'bookings_sample' => DB::table('bookings')->limit(1)->get()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
});
