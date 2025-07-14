<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Subject;
use App\Models\Location;
use App\Models\Booking;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ReviewSystemTestDataSeeder extends Seeder
{
    /**
     * Seed test data specifically for the review system.
     */
    public function run(): void
    {
        $this->command->info('🌱 Creating test data for review system...');

        // 1. Create or get test student
        $student = $this->createTestStudent();

        // 2. Create test tutors with subjects
        $tutors = $this->createTestTutors();

        // 3. Create test bookings (completed and upcoming)
        $this->createTestBookings($student, $tutors);

        $this->command->info('✅ Review system test data created successfully!');
        $this->printTestAccountInfo();
    }

    private function createTestStudent(): User
    {
        $student = User::updateOrCreate(
            ['email' => 'ana.student@test.com'],
            [
                'first_name' => 'Ana',
                'last_name' => 'Popescu',
                'password' => Hash::make('password123'),
                'user_type' => 'student',
                'phone' => '+40712345678',
                'is_active' => true,
            ]
        );

        $this->command->info("📚 Created student: {$student->full_name} ({$student->email})");
        return $student;
    }

    private function createTestTutors(): array
    {
        // Ensure we have locations
        $location = Location::firstOrCreate(
            ['city' => 'București', 'county' => 'București'],
            ['slug' => 'bucuresti', 'is_active' => true]
        );

        // Ensure we have subjects
        $subjects = $this->createTestSubjects();

        $tutorData = [
            [
                'email' => 'mihai.profesor@test.com',
                'first_name' => 'Mihai',
                'last_name' => 'Ionescu',
                'bio' => 'Profesor de matematică cu 10 ani experiență. Pasionat de predarea conceptelor complexe într-un mod simplu și accesibil.',
                'hourly_rate' => 75.00,
                'subjects' => ['Matematică', 'Fizică'],
            ],
            [
                'email' => 'elena.profesor@test.com',
                'first_name' => 'Elena',
                'last_name' => 'Dumitrescu',
                'bio' => 'Profesoară de engleză cu certificare Cambridge. Specializată în pregătirea pentru examene internaționale.',
                'hourly_rate' => 80.00,
                'subjects' => ['Engleză'],
            ],
            [
                'email' => 'alex.profesor@test.com',
                'first_name' => 'Alexandru',
                'last_name' => 'Popa',
                'bio' => 'Dezvoltator software cu experiență în predarea programării. Specializat în JavaScript și Python.',
                'hourly_rate' => 90.00,
                'subjects' => ['Informatică'],
            ],
            [
                'email' => 'maria.profesor@test.com',
                'first_name' => 'Maria',
                'last_name' => 'Georgescu',
                'bio' => 'Profesoară de română cu specializare în literatură. Pasionată de poezie și analiza textelor.',
                'hourly_rate' => 70.00,
                'subjects' => ['Română'],
            ],
        ];

        $tutors = [];
        foreach ($tutorData as $data) {
            // Create user
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'password' => Hash::make('password123'),
                    'user_type' => 'tutor',
                    'phone' => '+4072' . rand(1000000, 9999999),
                    'is_active' => true,
                ]
            );

            // Create tutor profile
            $tutor = Tutor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'location_id' => $location->id,
                    'bio' => $data['bio'],
                    'hourly_rate' => $data['hourly_rate'],
                    'rating' => rand(42, 50) / 10, // 4.2 to 5.0 rating
                    'total_reviews' => rand(15, 50),
                    'is_verified' => true,
                    'is_featured' => rand(0, 1),
                    'offers_online' => true,
                    'offers_in_person' => true,
                ]
            );

            // Attach subjects
            $tutorSubjects = [];
            foreach ($data['subjects'] as $subjectName) {
                $subject = $subjects->where('name', $subjectName)->first();
                if ($subject) {
                    $tutorSubjects[] = $subject->id;
                }
            }
            $tutor->subjects()->sync($tutorSubjects);

            $tutors[] = $user;
            $this->command->info("👨‍🏫 Created tutor: {$user->full_name} ({$user->email}) - " . implode(', ', $data['subjects']));
        }

        return $tutors;
    }

    private function createTestSubjects(): \Illuminate\Support\Collection
    {
        $subjectsData = [
            ['name' => 'Matematică', 'slug' => 'matematica', 'icon' => '📐'],
            ['name' => 'Engleză', 'slug' => 'engleza', 'icon' => '🇬🇧'],
            ['name' => 'Română', 'slug' => 'romana', 'icon' => '📚'],
            ['name' => 'Informatică', 'slug' => 'informatica', 'icon' => '💻'],
            ['name' => 'Fizică', 'slug' => 'fizica', 'icon' => '⚛️'],
        ];

        $subjects = collect();
        foreach ($subjectsData as $data) {
            $subject = Subject::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => "Lecții de {$data['name']} pentru toate nivelurile",
                    'icon' => $data['icon'],
                    'is_active' => true,
                ]
            );
            $subjects->push($subject);
        }

        return $subjects;
    }

    private function createTestBookings(User $student, array $tutors): void
    {
        $this->command->info('📅 Creating test bookings...');

        // Create completed bookings (some with reviews, some without)
        $this->createCompletedBookings($student, $tutors);

        // Create upcoming bookings
        $this->createUpcomingBookings($student, $tutors);
    }

    private function createCompletedBookings(User $student, array $tutors): void
    {
        $completedBookingsData = [
            // Bookings that NEED reviews (will show "Evaluează" buttons)
            [
                'tutor_email' => 'mihai.profesor@test.com',
                'subject' => 'Matematică',
                'days_ago' => 5,
                'price' => 75,
                'has_review' => false,
                'lesson_type' => 'online',
            ],
            [
                'tutor_email' => 'elena.profesor@test.com',
                'subject' => 'Engleză',
                'days_ago' => 3,
                'price' => 80,
                'has_review' => false,
                'lesson_type' => 'in_person',
            ],
            [
                'tutor_email' => 'alex.profesor@test.com',
                'subject' => 'Informatică',
                'days_ago' => 7,
                'price' => 90,
                'has_review' => false,
                'lesson_type' => 'online',
            ],

            // Bookings that HAVE reviews (will show stars + "Editează" buttons)
            [
                'tutor_email' => 'maria.profesor@test.com',
                'subject' => 'Română',
                'days_ago' => 10,
                'price' => 70,
                'has_review' => true,
                'review_data' => [
                    'rating' => 5,
                    'comment' => 'Profesoara Maria este fantastică! Explică foarte clar și are multă răbdare. M-a ajutat enorm la înțelegerea poeziei românești.'
                ],
                'lesson_type' => 'online',
            ],
            [
                'tutor_email' => 'mihai.profesor@test.com',
                'subject' => 'Fizică',
                'days_ago' => 12,
                'price' => 75,
                'has_review' => true,
                'review_data' => [
                    'rating' => 4,
                    'comment' => 'Lecție foarte bună de fizică. Profesorul explică conceptele pas cu pas și are exemple practice excelente.'
                ],
                'lesson_type' => 'in_person',
            ],
        ];

        foreach ($completedBookingsData as $data) {
            $tutor = collect($tutors)->first(fn($t) => $t->email === $data['tutor_email']);
            $subject = Subject::where('name', $data['subject'])->first();

            if (!$tutor || !$subject) {
                continue;
            }

            $scheduledAt = Carbon::now()->subDays($data['days_ago'])->setTime(rand(9, 17), [0, 30][rand(0, 1)]);
            $completedAt = $scheduledAt->copy()->addMinutes(90);

            $booking = Booking::create([
                'student_id' => $student->id,
                'tutor_id' => $tutor->id,
                'subject_id' => $subject->id,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => 90,
                'lesson_type' => $data['lesson_type'],
                'price' => $data['price'],
                'status' => 'completed',
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'student_notes' => 'Doresc să îmbunătățesc înțelegerea conceptelor de bază.',
                'confirmed_at' => $scheduledAt->copy()->subDays(1),
                'completed_at' => $completedAt,
            ]);

            // Create review if specified
            if ($data['has_review']) {
                Review::create([
                    'booking_id' => $booking->id,
                    'student_id' => $student->id,
                    'tutor_id' => $tutor->id,
                    'rating' => $data['review_data']['rating'],
                    'comment' => $data['review_data']['comment'],
                    'created_at' => $completedAt->copy()->addHours(2),
                ]);

                $this->command->info("✅ Created completed booking with review: {$subject->name} cu {$tutor->full_name}");
            } else {
                $this->command->info("⭐ Created completed booking needing review: {$subject->name} cu {$tutor->full_name}");
            }
        }
    }

    private function createUpcomingBookings(User $student, array $tutors): void
    {
        $upcomingBookingsData = [
            [
                'tutor_email' => 'elena.profesor@test.com',
                'subject' => 'Engleză',
                'days_ahead' => 2,
                'price' => 80,
                'status' => 'confirmed',
                'lesson_type' => 'online',
            ],
            [
                'tutor_email' => 'alex.profesor@test.com',
                'subject' => 'Informatică',
                'days_ahead' => 5,
                'price' => 90,
                'status' => 'pending',
                'lesson_type' => 'online',
            ],
            [
                'tutor_email' => 'mihai.profesor@test.com',
                'subject' => 'Matematică',
                'days_ahead' => 7,
                'price' => 75,
                'status' => 'confirmed',
                'lesson_type' => 'in_person',
            ],
        ];

        foreach ($upcomingBookingsData as $data) {
            $tutor = collect($tutors)->first(fn($t) => $t->email === $data['tutor_email']);
            $subject = Subject::where('name', $data['subject'])->first();

            if (!$tutor || !$subject) {
                continue;
            }

            $scheduledAt = Carbon::now()->addDays($data['days_ahead'])->setTime(rand(9, 17), [0, 30][rand(0, 1)]);

            $booking = Booking::create([
                'student_id' => $student->id,
                'tutor_id' => $tutor->id,
                'subject_id' => $subject->id,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => 90,
                'lesson_type' => $data['lesson_type'],
                'price' => $data['price'],
                'status' => $data['status'],
                'payment_method' => 'card',
                'payment_status' => $data['status'] === 'confirmed' ? 'paid' : 'pending',
                'student_notes' => 'Aștept cu nerăbdare această lecție!',
                'confirmed_at' => $data['status'] === 'confirmed' ? now() : null,
            ]);

            $this->command->info("📅 Created upcoming booking: {$subject->name} cu {$tutor->full_name} ({$data['status']})");
        }
    }

    private function printTestAccountInfo(): void
    {
        $this->command->info('');
        $this->command->info('🎉 Test data created successfully!');
        $this->command->info('');
        $this->command->info('📧 TEST ACCOUNTS:');
        $this->command->info('┌─────────────────────────────────────────────────────┐');
        $this->command->info('│ STUDENT ACCOUNT                                     │');
        $this->command->info('│ Email: ana.student@test.com                         │');
        $this->command->info('│ Password: password123                               │');
        $this->command->info('│ Name: Ana Popescu                                   │');
        $this->command->info('├─────────────────────────────────────────────────────┤');
        $this->command->info('│ TUTOR ACCOUNTS                                      │');
        $this->command->info('│ mihai.profesor@test.com / password123               │');
        $this->command->info('│ elena.profesor@test.com / password123               │');
        $this->command->info('│ alex.profesor@test.com / password123                │');
        $this->command->info('│ maria.profesor@test.com / password123               │');
        $this->command->info('└─────────────────────────────────────────────────────┘');
        $this->command->info('');
        $this->command->info('📊 DATA CREATED:');
        $this->command->info('• 3 completed bookings WITHOUT reviews (⭐ will show "Evaluează" buttons)');
        $this->command->info('• 2 completed bookings WITH reviews (⭐ will show stars + "Editează" buttons)');
        $this->command->info('• 3 upcoming bookings (confirmed and pending)');
        $this->command->info('• 4 tutors with different subjects');
        $this->command->info('• 5 subjects (Matematică, Engleză, Română, Informatică, Fizică)');
        $this->command->info('');
        $this->command->info('🚀 NEXT STEPS:');
        $this->command->info('1. Login as student: ana.student@test.com / password123');
        $this->command->info('2. Go to student dashboard');
        $this->command->info('3. Look for "Evaluează" buttons in Recent Lessons');
        $this->command->info('4. Test the review system!');
        $this->command->info('');
    }
}
