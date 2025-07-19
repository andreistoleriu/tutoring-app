<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Subject;
use App\Models\Location;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;

class TutorSeeder extends Seeder
{
    public function run(): void
    {
        $tutors = [
            [
                'first_name' => 'Maria',
                'last_name' => 'Popescu',
                'email' => 'maria.popescu@tutoring.ro',
                'bio' => 'Profesor de matematică cu 10 ani de experiență. Pasionată de predarea conceptelor complexe într-un mod simplu și accesibil.',
                'hourly_rate' => 75,
                'offers_online' => true,
                'offers_in_person' => true,
                'experience' => 'Licență în Matematică, Universitatea București. 10 ani experiență în predare.',
                'education' => 'Universitatea București - Matematică, Master în Didactica Matematicii',
                'location_id' => 1, // București Sector 1
                'subjects' => [1, 20], // Matematică, Pregătire BAC
                'rating' => 4.9,
                'total_reviews' => 127,
                'total_lessons' => 340,
                'is_verified' => true,
                'is_featured' => true,
            ],
            [
                'first_name' => 'Ion',
                'last_name' => 'Ionescu',
                'email' => 'ion.ionescu@tutoring.ro',
                'bio' => 'Developer senior cu experiență în predarea programării. Specializat în Python, JavaScript și dezvoltare web.',
                'hourly_rate' => 120,
                'offers_online' => true,
                'offers_in_person' => false,
                'experience' => 'Software Engineer la Google România, 8 ani experiență în dezvoltare.',
                'education' => 'Universitatea Tehnică Cluj-Napoca - Informatică',
                'location_id' => 13, // Cluj-Napoca
                'subjects' => [10, 11, 12], // Informatică, Programare, Baze de Date
                'rating' => 4.8,
                'total_reviews' => 89,
                'total_lessons' => 245,
                'is_verified' => true,
                'is_featured' => true,
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Georgescu',
                'email' => 'ana.georgescu@tutoring.ro',
                'bio' => 'Profesor nativ de limba engleză cu certificare CELTA. Specializată în pregătirea pentru examene internaționale.',
                'hourly_rate' => 90,
                'offers_online' => true,
                'offers_in_person' => true,
                'experience' => 'Certificare CELTA, 12 ani experiență în predarea limbii engleze.',
                'education' => 'University of Cambridge - English Literature, CELTA Certification',
                'location_id' => 1, // București
                'subjects' => [6], // Engleză
                'rating' => 4.9,
                'total_reviews' => 156,
                'total_lessons' => 420,
                'is_verified' => true,
                'is_featured' => true,
            ]
        ];

        foreach ($tutors as $tutorData) {
            // Create user
            $user = User::firstOrCreate([
                'email' => $tutorData['email']
            ], [
                'password' => Hash::make('password123'),
                'first_name' => $tutorData['first_name'],
                'last_name' => $tutorData['last_name'],
                'user_type' => 'tutor',
                'is_active' => true,
            ]);

            // Create tutor profile
            $tutor = Tutor::firstOrCreate([
                'user_id' => $user->id
            ], [
                'location_id' => $tutorData['location_id'],
                'bio' => $tutorData['bio'],
                'hourly_rate' => $tutorData['hourly_rate'],
                'offers_online' => $tutorData['offers_online'],
                'offers_in_person' => $tutorData['offers_in_person'],
                'experience' => $tutorData['experience'],
                'education' => $tutorData['education'],
                'rating' => $tutorData['rating'],
                'total_reviews' => $tutorData['total_reviews'],
                'total_lessons' => $tutorData['total_lessons'],
                'total_earnings' => $tutorData['total_lessons'] * $tutorData['hourly_rate'],
                'is_verified' => $tutorData['is_verified'],
                'is_featured' => $tutorData['is_featured'],
                'is_active' => true,
                'last_active' => now(),
            ]);

            // Attach subjects
            if (!empty($tutorData['subjects'])) {
                $tutor->subjects()->sync($tutorData['subjects']);
            }

            // Create subscription - FIXED: Use user_id instead of tutor_id
            Subscription::firstOrCreate([
                'user_id' => $user->id
            ], [
                'plan_type' => $tutorData['is_featured'] ? 'premium' : 'free_trial',
                'price' => $tutorData['is_featured'] ? 49.99 : 0,
                'currency' => 'EUR',
                'status' => 'active',
                'started_at' => now()->subDays(rand(30, 365)),
                'expires_at' => now()->addDays(30),
                'shows_ads' => !$tutorData['is_featured'],
            ]);
        }

        $this->command->info('✅ Tutors seeded successfully!');
    }
}
