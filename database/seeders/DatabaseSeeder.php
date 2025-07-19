<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SubjectSeeder::class,
            LocationSeeder::class,
            TutorSeeder::class,
            StudentTestDataSeeder::class,
            AdsSeeder::class,
        ]);
    }
}
