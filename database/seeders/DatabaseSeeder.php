<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Service;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        Schedule::truncate();
        Service::truncate();
        User::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create admin user
        User::create([
            'name' => 'Admin Cizy Nails',
            'email' => 'admin@cizy.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        // Create customer user
        User::create([
            'name' => 'Customer User',
            'email' => 'user@cizy.com',
            'password' => bcrypt('user123'),
            'role' => 'customer',
        ]);

        $nailsArtNatural = Service::create([
            'name' => 'Nails Art (Kuku Asli)',
            'type' => 'nails_art',
            'subtype' => 'natural',
            'duration_minutes' => 90,
            'price' => 50000,
            'staff_count' => 2,
            'description' => 'Layanan nail art untuk kuku asli dengan desain custom',
            'is_active' => true,
        ]);

        $nailsArtExtension = Service::create([
            'name' => 'Nails Art (Extension)',
            'type' => 'nails_art',
            'subtype' => 'extension',
            'duration_minutes' => 150,
            'price' => 50000,
            'staff_count' => 2,
            'description' => 'Layanan nail art dengan extension untuk hasil yang lebih panjang',
            'is_active' => true,
        ]);

        $eyelash = Service::create([
            'name' => 'Eyelash Service',
            'type' => 'eyelash',
            'subtype' => null,
            'duration_minutes' => 90,
            'price' => 50000,
            'staff_count' => 1,
            'description' => 'Layanan eyelash extension profesional',
            'is_active' => true,
        ]);

        $timeSlots = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'];
        
        for ($day = 0; $day < 30; $day++) {
            $date = Carbon::now()->addDays($day)->toDateString();
            
            foreach ($timeSlots as $timeSlot) {
                Schedule::create([
                    'date' => $date,
                    'time_slot' => $timeSlot,
                    'nails_art_booked' => 0,
                    'eyelash_booked' => 0,
                ]);
            }
        }
    }
}
