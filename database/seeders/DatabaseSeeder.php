<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test guide user
        User::factory()->create([
            'name' => 'Guide Test',
            'email' => 'guide@gema.test',
            'password' => bcrypt('password'),
        ]);

        // Seed prayers
        $this->call(PrayerSeeder::class);
    }
}
