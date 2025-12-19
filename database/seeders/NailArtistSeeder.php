<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class NailArtistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Cizy Nails Artist',
            'email' => 'cizynails@cizynails.com',
            'password' => Hash::make('cizynails123'),
            'phone' => '0812-3456-789',
            'role' => 'nail_artist',
        ]);
    }
}
