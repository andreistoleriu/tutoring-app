<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Subject;
use App\Models\Location;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Availability;
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
        }

        // Ensure we have locations
        $locations = Location::all();
        if ($locations->isEmpty()) {
            $locations = collect([
                Location::create(['county' => 'București', 'city' => 'București', 'slug' => 'bucuresti', 'is_active' => true]),
                Location::create(['county' => 'Cluj', 'city' => 'Cluj-Napoca', 'slug' => 'cluj-napoca', 'is_active' => true]),
                Location::create(['county' => 'Iași', 'city' => 'Iași', 'slug' => 'iasi', 'is_active' => true]),
            ]);
        }

        // Ensure we have subjects
        $subjects = Subject::all();
        if ($subjects->isEmpty()) {
            $subjects = collect([
                Subject::create(['name' => 'Matematică', 'slug' => 'matematica', 'description' => 'Matematică pentru toate nivelurile', 'icon' => '📐', 'is_active' => true]),
                Subject::create(['name' => 'Engleză', 'slug' => 'engleza', 'description' => 'Limba engleză', 'icon' => '🇬🇧', 'is_active' => true]),
                Subject::create(['name' => 'Română', 'slug' => 'romana', 'description' => 'Limba și literatura română', 'icon' => '📚', 'is_active' => true]),
                Subject::create(['name' => 'Informatică', 'slug' => 'informatica', 'description' => 'Programare și informatică', 'icon' => '💻', 'is_active' => true]),
                Subject::create(['name' => 'Fizică', 'slug' => 'fizica', 'description' => 'Fizică pentru liceu', 'icon' => '⚛️', 'is_active' => true]),
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
                        'experience_description' => 'Experiență de ' . rand(3, 10) . ' ani în predarea acestei materii'
                    ]);
                }

                $tutors[] = $tutor;
            } else {
                $tutors[] = $tutorUser->tutor;
            }
        }

        // Create test bookings with various statuses and dates
        $this->createTestBookings($student, $tutors, $subjects);

        $this->command->info('✅ Student test data created successfully!');
        $this->command->info('📧 Student login: student@test.com / password');
        $this->command->info('🎯 Created bookings with various statuses for realistic dashboard testing');
    }

    private function createTestBookings($student, $tutors, $subjects)
    {
        $bookingData = [
            // Completed bookings (last month and this month)
            [
                'status' => 'completed',
                'scheduled_at' => Carbon::now()->subDays(25),
                'completed_at' => Carbon::now()->subDays(25)->addHours(1),
                'payment_status' => 'paid',
                'has_review' => true,
                'review_rating' => 5,
                'review_comment' => 'Lecție excelentă! Mihai explică foarte clar conceptele de matematică.'
            ],
            [
                'status' => 'completed',
                'scheduled_at' => Carbon::now()->subDays(18),
                'completed_at' => Carbon::now()->subDays(18)->addHours(1),
                'payment_status' => 'paid',
                'has_review' => true,
                'review_rating' => 4,
                'review_comment' => 'Foarte bună lecția de engleză, am învățat multe cuvinte noi.'
            ],
            [
                'status' => 'completed',
                'scheduled_at' => Carbon::now()->subDays(12),
                'completed_at' => Carbon::now()->subDays(12)->addHours(1),
                'payment_status' => 'paid',
                'has_review' => false,
            ],
            [
                'status' => 'completed',
                'scheduled_at' => Carbon::now()->subDays(8),
                'completed_at' => Carbon::now()->subDays(8)->addHours(1),
                'payment_status' => 'paid',
                'has_review' => true,
                'review_rating' => 5,
                'review_comment' => 'Andrei m-a ajutat să înțeleg programarea orientată pe obiecte. Foarte util!'
            ],
            [
                'status' => 'completed',
                'scheduled_at' => Carbon::now()->subDays(4),
                'completed_at' => Carbon::now()->subDays(4)->addHours(1),
                'payment_status' => 'paid',
                'has_review' => false,
            ],
            [
                'status' => 'completed',
                'scheduled_at' => Carbon::now()->subDays(2),
                'completed_at' => Carbon::now()->subDays(2)->addHours(1),
                'payment_status' => 'paid',
                'has_review' => false,
            ],

            // Confirmed bookings (upcoming this week)
            [
                'status' => 'confirmed',
                'scheduled_at' => Carbon::now()->addDays(2)->setTime(14, 0),
                'payment_status' => 'pending',
            ],
            [
                'status' => 'confirmed',
                'scheduled_at' => Carbon::now()->addDays(4)->setTime(16, 0),
                'payment_status' => 'pending',
            ],
            [
                'status' => 'confirmed',
                'scheduled_at' => Carbon::now()->addDays(6)->setTime(10, 0),
                'payment_status' => 'pending',
            ],

            // Pending bookings
            [
                'status' => 'pending',
                'scheduled_at' => Carbon::now()->addDays(8)->setTime(15, 0),
                'payment_status' => 'pending',
            ],
            [
                'status' => 'pending',
                'scheduled_at' => Carbon::now()->addDays(10)->setTime(11, 0),
                'payment_status' => 'pending',
            ],

            // One cancelled booking
            [
                'status' => 'cancelled',
                'scheduled_at' => Carbon::now()->subDays(5),
                'cancelled_at' => Carbon::now()->subDays(6),
                'payment_status' => 'refunded',
            ],
        ];

        foreach ($bookingData as $index => $data) {
            $tutor = $tutors[array_rand($tutors)];
            $subject = $subjects->random();

            $booking = Booking::create([
                'student_id' => $student->id,
                'tutor_id' => $tutor->user_id,
                'subject_id' => $subject->id,
                'scheduled_at' => $data['scheduled_at'],
                'duration_minutes' => 60,
                'lesson_type' => rand(0, 1) ? 'online' : 'in_person',
                'price' => $tutor->hourly_rate,
                'status' => $data['status'],
                'payment_method' => rand(0, 1) ? 'card' : 'cash',
                'payment_status' => $data['payment_status'],
                'student_notes' => $index % 3 === 0 ? 'Aș vrea să ne concentrăm pe exercițiile de la examen.' : null,
                'tutor_notes' => $data['status'] === 'completed' ? 'Student foarte aplicat, progres excelent!' : null,
                'confirmed_at' => in_array($data['status'], ['confirmed', 'completed']) ? $data['scheduled_at']->subDays(1) : null,
                'completed_at' => $data['completed_at'] ?? null,
                'cancelled_at' => $data['cancelled_at'] ?? null,
                'cancellation_reason' => $data['status'] === 'cancelled' ? 'Schimbare în program' : null,
            ]);

            // Create review if specified
            if (isset($data['has_review']) && $data['has_review']) {
                Review::create([
                    'booking_id' => $booking->id,
                    'student_id' => $student->id,
                    'tutor_id' => $tutor->user_id,
                    'rating' => $data['review_rating'],
                    'comment' => $data['review_comment'],
                    'created_at' => $booking->completed_at->addMinutes(rand(30, 180)),
                ]);
            }
        }

        $this->command->info('📚 Created ' . count($bookingData) . ' test bookings');
        $this->command->info('✅ Completed lessons: ' . collect($bookingData)->where('status', 'completed')->count());
        $this->command->info('📅 Upcoming lessons: ' . collect($bookingData)->whereIn('status', ['confirmed', 'pending'])->count());
        $this->command->info('⭐ Reviews created: ' . collect($bookingData)->where('has_review', true)->count());
    }
}
