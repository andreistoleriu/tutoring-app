<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Subject;
use App\Models\Location;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Availability;
use App\Models\Subscription;
use Carbon\Carbon;

class StudentTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds for student test data.
     */
    public function run(): void
    {
        // Get or create a test student
        $student = User::where('email', 'student@test.com')->first();

        if (!$student) {
            $student = User::create([
                'email' => 'student@test.com',
                'password' => bcrypt('password'),
                'first_name' => 'Ana',
                'last_name' => 'Popescu',
                'phone' => '0712345678',
                'user_type' => 'student',
                'is_active' => true,
            ]);

            // Create subscription for student - FIXED: Use user_id
            Subscription::create([
                'user_id' => $student->id,
                'plan_type' => 'free_trial',
                'price' => 0,
                'currency' => 'EUR',
                'status' => 'active',
                'started_at' => now(),
                'trial_ends_at' => now()->addDays(14),
                'expires_at' => now()->addDays(14),
                'shows_ads' => true,
            ]);
        }

        // Ensure we have locations
        $locations = Location::all();
        if ($locations->isEmpty()) {
            $locations = collect([
                Location::firstOrCreate(['slug' => 'bucuresti'], [
                    'county' => 'București',
                    'city' => 'București',
                    'is_active' => true
                ]),
                Location::firstOrCreate(['slug' => 'cluj-napoca'], [
                    'county' => 'Cluj',
                    'city' => 'Cluj-Napoca',
                    'is_active' => true
                ]),
                Location::firstOrCreate(['slug' => 'iasi'], [
                    'county' => 'Iași',
                    'city' => 'Iași',
                    'is_active' => true
                ]),
            ]);
        }

        // Ensure we have subjects
        $subjects = Subject::all();
        if ($subjects->isEmpty()) {
            $subjects = collect([
                Subject::firstOrCreate(['slug' => 'matematica'], [
                    'name' => 'Matematică',
                    'description' => 'Matematică pentru toate nivelurile',
                    'icon' => '📐',
                    'is_active' => true
                ]),
                Subject::firstOrCreate(['slug' => 'engleza'], [
                    'name' => 'Engleză',
                    'description' => 'Limba engleză',
                    'icon' => '🇬🇧',
                    'is_active' => true
                ]),
                Subject::firstOrCreate(['slug' => 'romana'], [
                    'name' => 'Română',
                    'description' => 'Limba și literatura română',
                    'icon' => '📚',
                    'is_active' => true
                ]),
                Subject::firstOrCreate(['slug' => 'informatica'], [
                    'name' => 'Informatică',
                    'description' => 'Programare și informatică',
                    'icon' => '💻',
                    'is_active' => true
                ]),
                Subject::firstOrCreate(['slug' => 'fizica'], [
                    'name' => 'Fizică',
                    'description' => 'Fizică pentru liceu',
                    'icon' => '⚛️',
                    'is_active' => true
                ]),
            ]);
        }

        // Create test tutors
        $tutors = [];
        $tutorData = [
            [
                'email' => 'tutor1@test.com',
                'first_name' => 'Mihai',
                'last_name' => 'Ionescu',
                'bio' => 'Profesor de matematică cu 10 ani experiență',
                'hourly_rate' => 75.00,
                'subjects' => ['Matematică', 'Fizică']
            ],
            [
                'email' => 'tutor2@test.com',
                'first_name' => 'Elena',
                'last_name' => 'Georgescu',
                'bio' => 'Specialist în limba engleză, certificat Cambridge',
                'hourly_rate' => 80.00,
                'subjects' => ['Engleză']
            ],
            [
                'email' => 'tutor3@test.com',
                'first_name' => 'Andrei',
                'last_name' => 'Marin',
                'bio' => 'Developer și profesor de informatică',
                'hourly_rate' => 90.00,
                'subjects' => ['Informatică']
            ],
            [
                'email' => 'tutor4@test.com',
                'first_name' => 'Maria',
                'last_name' => 'Dumitrescu',
                'bio' => 'Profesoară de română cu pasiune pentru literatură',
                'hourly_rate' => 70.00,
                'subjects' => ['Română']
            ]
        ];

        foreach ($tutorData as $data) {
            $tutorUser = User::where('email', $data['email'])->first();

            if (!$tutorUser) {
                $tutorUser = User::create([
                    'email' => $data['email'],
                    'password' => bcrypt('password'),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => '071' . rand(1000000, 9999999),
                    'user_type' => 'tutor',
                    'is_active' => true,
                ]);

                $tutor = Tutor::create([
                    'user_id' => $tutorUser->id,
                    'location_id' => $locations->random()->id,
                    'bio' => $data['bio'],
                    'hourly_rate' => $data['hourly_rate'],
                    'offers_online' => true,
                    'offers_in_person' => true,
                    'experience' => 'Experiență de ' . rand(3, 15) . ' ani în predare',
                    'education' => 'Universitatea din București - Licență și Master',
                    'rating' => rand(42, 50) / 10, // 4.2 to 5.0
                    'total_reviews' => rand(15, 50),
                    'total_lessons' => rand(100, 500),
                    'total_earnings' => rand(5000, 25000),
                    'is_verified' => true,
                    'is_featured' => rand(0, 1),
                    'is_active' => true,
                    'last_active' => now(),
                ]);

                // Attach subjects
                $tutorSubjects = $subjects->whereIn('name', $data['subjects']);
                foreach ($tutorSubjects as $subject) {
                    $tutor->subjects()->attach($subject->id, [
                        'experience_description' => 'Experiență de ' . rand(3, 10) . ' ani în predarea acestei materii',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Create subscription for tutor - FIXED: Use user_id instead of tutor_id
                Subscription::create([
                    'user_id' => $tutorUser->id, // FIXED: Changed from tutor_id
                    'plan_type' => $tutor->is_featured ? 'premium' : 'free_trial',
                    'price' => $tutor->is_featured ? 49.99 : 0,
                    'currency' => 'EUR',
                    'status' => 'active',
                    'started_at' => now()->subDays(rand(30, 365)),
                    'expires_at' => now()->addDays(30),
                    'shows_ads' => !$tutor->is_featured,
                ]);

                $tutors[] = $tutor;
            }
        }

        // Create sample bookings
        $tutorUsers = User::where('user_type', 'tutor')->get();
        $mathSubject = $subjects->where('name', 'Matematică')->first();
        $englishSubject = $subjects->where('name', 'Engleză')->first();

        // Create some completed bookings with reviews
        for ($i = 0; $i < 5; $i++) {
            $tutor = $tutorUsers->random();
            $tutorProfile = $tutor->tutor;

            if (!$tutorProfile || $tutorProfile->subjects->isEmpty()) {
                continue;
            }

            $subject = $tutorProfile->subjects->random();

            $booking = Booking::create([
                'student_id' => $student->id,
                'tutor_id' => $tutor->id,
                'subject_id' => $subject->id,
                'scheduled_at' => now()->subDays(rand(7, 30))->addHours(rand(9, 17)),
                'duration_minutes' => [60, 90, 120][rand(0, 2)],
                'lesson_type' => ['online', 'in_person'][rand(0, 1)],
                'price' => $tutorProfile->hourly_rate,
                'status' => 'completed',
                'payment_method' => ['card', 'cash'][rand(0, 1)],
                'payment_status' => 'paid',
                'student_notes' => 'Vreau să îmbunătățesc înțelegerea conceptelor de bază.',
                'confirmed_at' => now()->subDays(rand(8, 31)),
                'completed_at' => now()->subDays(rand(1, 7)),
            ]);

            // Create a review for completed booking
            Review::create([
                'booking_id' => $booking->id,
                'student_id' => $student->id,
                'tutor_id' => $tutor->id,
                'rating' => rand(4, 5),
                'comment' => [
                    'Profesorul a fost foarte răbdător și a explicat foarte clar.',
                    'Lecția a fost excelentă, am înțeles mult mai bine materia.',
                    'Recomand cu încredere acest profesor!',
                    'Foarte profesionist și prietenos.',
                    'Explicații clare și exerciții utile.'
                ][rand(0, 4)]
            ]);
        }

        // Create some upcoming bookings
        for ($i = 0; $i < 3; $i++) {
            $tutor = $tutorUsers->random();
            $tutorProfile = $tutor->tutor;

            if (!$tutorProfile || $tutorProfile->subjects->isEmpty()) {
                continue;
            }

            $subject = $tutorProfile->subjects->random();

            Booking::create([
                'student_id' => $student->id,
                'tutor_id' => $tutor->id,
                'subject_id' => $subject->id,
                'scheduled_at' => now()->addDays(rand(1, 14))->addHours(rand(9, 17)),
                'duration_minutes' => [60, 90, 120][rand(0, 2)],
                'lesson_type' => ['online', 'in_person'][rand(0, 1)],
                'price' => $tutorProfile->hourly_rate,
                'status' => 'confirmed',
                'payment_method' => ['card', 'cash'][rand(0, 1)],
                'payment_status' => 'pending',
                'student_notes' => 'Aștept cu nerăbdare această lecție!',
                'confirmed_at' => now(),
            ]);
        }

        $this->command->info('✅ Student test data seeded successfully!');
    }
}
