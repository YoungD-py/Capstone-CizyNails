<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebugController extends Controller
{
    public function users()
    {
        $users = User::select('id', 'name', 'email', 'phone', 'role')->limit(20)->get();
        
        // Also show column info
        $columns = DB::select("SHOW COLUMNS FROM users");
        $columnNames = collect($columns)->pluck('Field')->toArray();
        
        return view('debug.users', compact('users', 'columnNames'));
    }

    public function updatePhones()
    {
        // Update all users with null phone with a test phone number
        $updated = User::whereNull('phone')->update(['phone' => '0812-3456-789']);
        
        $users = User::select('id', 'name', 'email', 'phone', 'role')->limit(20)->get();
        $columnNames = DB::select("SHOW COLUMNS FROM users");
        $columnNames = collect($columnNames)->pluck('Field')->toArray();
        
        return view('debug.users', compact('users', 'columnNames'))->with('message', "Updated $updated users with phone number");
    }

    public function fixAllPhones()
    {
        // Update ALL users with a phone if they don't have one
        $updated = User::update(['phone' => DB::raw("COALESCE(phone, '0812-3456-789')")]);
        
        $users = User::select('id', 'name', 'email', 'phone', 'role')->get();
        $columnNames = DB::select("SHOW COLUMNS FROM users");
        $columnNames = collect($columnNames)->pluck('Field')->toArray();
        
        return view('debug.users', compact('users', 'columnNames'))->with('message', "Fixed all users!");
    }
}

