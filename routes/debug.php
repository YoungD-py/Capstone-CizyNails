<?php

use App\Models\User;

Route::get('/debug/users', function() {
    $users = User::select('id', 'name', 'email', 'phone', 'role')->limit(10)->get();
    return response()->json($users);
});

Route::get('/debug/auth-user', function() {
    if (auth()->check()) {
        return response()->json([
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'phone' => auth()->user()->phone,
            'role' => auth()->user()->role,
        ]);
    }
    return response()->json(['message' => 'Not authenticated']);
});
