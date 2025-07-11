<?php
// Create: database/seeders/TutorWithAvailabilitySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Location;
use App\Models\Subject;
use App\Models\Availability;
use App\Models\Review;
use Illuminate\Support\Facades\Hash;

class TutorWithAvailabilitySeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Creating tutor with full availability...');

        // Create location if it doesn't exist
        $location = Location::firstOrCreate([
            'city' => 'București',
            'county' => 'București'
        ], [
            'slug' => 'bucuresti',
            'is_active' => true,
        ]);

        // Create subjects if they don't exist
        $subjects = [
            [
                'name' => 'Matematică',
                'slug' => 'matematica',
                'description' => 'Matematică pentru toate nivelurile - algebră, geometrie, analiză',
                'icon' => '📐'
            ],
            [
                'name' => 'Fizică',
                'slug' => 'fizica',
                'description' => 'Fizică pentru liceu și facultate - mecanică, termodinamică, optică',
                'icon' => '⚡'
            ],
            [
                'name' => 'Programare',
                'slug' => 'programare',
                'description' => 'Programare - Python, JavaScript, Web Development',
                'icon' => '💻'
            ],
            [
                'name' => 'Informatică',
                'slug' => 'informatica',
                'description' => 'Informatică pentru BAC și admitere facultate',
                'icon' => '🖥️'
            ]
        ];

        $createdSubjects = [];
        foreach ($subjects as $subjectData) {
            $subject = Subject::firstOrCreate([
                'slug' => $subjectData['slug']
            ], [
                'name' => $subjectData['name'],
                'description' => $subjectData['description'],
                'icon' => $subjectData['icon'],
                'is_active' => true,
            ]);
            $createdSubjects[] = $subject;
        }

        // Create tutor user
        $tutorUser = User::firstOrCreate([
            'email' => 'elena.dumitrescu@example.com',
        ], [
            'first_name' => 'Elena',
            'last_name' => 'Dumitrescu',
            'password' => Hash::make('password123'),
            'user_type' => 'tutor',
            'phone' => '+40722345678',
            'is_active' => true,
        ]);

        // Create tutor profile
        $tutor = Tutor::firstOrCreate([
            'user_id' => $tutorUser->id,
        ], [
            'location_id' => $location->id,
            'bio' => 'Profesor de matematică cu 10 ani de experiență. Pasionată de predarea conceptelor complexe într-un mod simplu și accesibil.',
            'hourly_rate' => 75.00,
            'offers_online' => true,
            'offers_in_person' => true,
            'experience' => 'Licență în Matematică, Universitatea București. 10 ani experiență în predare.',
            'education' => 'Universitatea București - Matematică, Master în Didactica Matematicii',
            'rating' => 4.8,
            'total_reviews' => 156,
            'total_lessons' => 340,
            'is_verified' => true,
            'is_featured' => true,
            'is_active' => true,
            'last_active' => now(),
        ]);

        // Attach subjects to tutor with experience descriptions
        $tutor->subjects()->syncWithoutDetaching([
            $createdSubjects[0]->id => [
                'experience_description' => 'Specializată în algebră și geometrie pentru liceu și BAC'
            ],
            $createdSubjects[1]->id => [
                'experience_description' => 'Mecanică și termodinamică pentru clasa a XI-a și a XII-a'
            ],
            $createdSubjects[2]->id => [
                'experience_description' => 'Python și JavaScript pentru începători și nivel intermediar'
            ]
        ]);

        // Clear existing availability
        $tutor->availabilities()->delete();

        // Create comprehensive availability schedule
        $availabilitySchedule = [
            'monday' => [
                ['start' => '09:00', 'end' => '12:00', 'type' => 'both'],
                ['start' => '14:00', 'end' => '18:00', 'type' => 'both']
            ],
            'tuesday' => [
                ['start' => '10:00', 'end' => '13:00', 'type' => 'online'],
                ['start' => '15:00', 'end' => '19:00', 'type' => 'both']
            ],
            'wednesday' => [
                ['start' => '09:00', 'end' => '12:00', 'type' => 'both'],
                ['start' => '14:00', 'end' => '17:00', 'type' => 'in_person']
            ],
            'thursday' => [
                ['start' => '10:00', 'end' => '13:00', 'type' => 'both'],
                ['start' => '16:00', 'end' => '20:00', 'type' => 'online']
            ],
            'friday' => [
                ['start' => '09:00', 'end' => '12:00', 'type' => 'both'],
                ['start' => '14:00', 'end' => '18:00', 'type' => 'both']
            ],
            'saturday' => [
                ['start' => '10:00', 'end' => '14:00', 'type' => 'both']
            ]
        ];

        foreach ($availabilitySchedule as $day => $timeSlots) {
            foreach ($timeSlots as $slot) {
                Availability::create([
                    'tutor_id' => $tutorUser->id, // Use user_id as tutor_id
                    'day_of_week' => $day,
                    'start_time' => $slot['start'],
                    'end_time' => $slot['end'],
                    'lesson_type' => $slot['type'],
                    'is_active' => true,
                ]);
            }
        }

        // Create a few sample reviews
        $this->createSampleReviews($tutor, $createdSubjects);

        // Create another tutor with different availability
        $this->createSecondTutor($location, $createdSubjects);

        $this->command->info('✅ Tutor created successfully!');
        $this->command->info('📧 Email: elena.dumitrescu@example.com');
        $this->command->info('🔑 Password: password123');
        $this->command->info('🆔 User ID: ' . $tutorUser->id);
        $this->command->info('🔗 Profile URL: /tutors/' . $tutorUser->id);
        $this->command->info('');
        $this->command->info('📅 Availability Schedule:');
        $this->command->info('   Monday: 09:00-12:00, 14:00-18:00 (Both)');
        $this->command->info('   Tuesday: 10:00-13:00 (Online), 15:00-19:00 (Both)');
        $this->command->info('   Wednesday: 09:00-12:00 (Both), 14:00-17:00 (In Person)');
        $this->command->info('   Thursday: 10:00-13:00 (Both), 16:00-20:00 (Online)');
        $this->command->info('   Friday: 09:00-12:00, 14:00-18:00 (Both)');
        $this->command->info('   Saturday: 10:00-14:00 (Both)');
    }

    private function createSampleReviews($tutor, $subjects)
    {
        // Create a few student users for reviews
        $students = [
            [
                'first_name' => 'Ana',
                'last_name' => 'Popescu',
                'email' => 'ana.popescu@example.com'
            ],
            [
                'first_name' => 'Mihai',
                'last_name' => 'Ionescu',
                'email' => 'mihai.ionescu@example.com'
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Georgescu',
                'email' => 'maria.georgescu@example.com'
            ]
        ];

        foreach ($students as $index => $studentData) {
            $student = User::firstOrCreate([
                'email' => $studentData['email']
            ], [
                'first_name' => $studentData['first_name'],
                'last_name' => $studentData['last_name'],
                'password' => Hash::make('password123'),
                'user_type' => 'student',
                'is_active' => true,
            ]);

            // Create a completed booking for each student
            $booking = \App\Models\Booking::create([
                'student_id' => $student->id,
                'tutor_id' => $tutor->user_id,
                'subject_id' => $subjects[$index % count($subjects)]->id,
                'scheduled_at' => now()->subDays(rand(1, 30)),
                'duration_minutes' => 60,
                'lesson_type' => ['online', 'in_person'][rand(0, 1)],
                'price' => $tutor->hourly_rate,
                'status' => 'completed',
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'completed_at' => now()->subDays(rand(1, 30)),
            ]);

            // Create review
            $reviews = [
                'Explicații clare și exerciții utile. Recomand cu încredere!',
                'Foarte răbdătoare și profesionistă. M-a ajutat mult la matematică.',
                'Excelentă profesoară! Am reușit să înțeleg concepte pe care le considerăm imposibile.'
            ];

            Review::create([
                'booking_id' => $booking->id,
                'student_id' => $student->id,
                'tutor_id' => $tutor->user_id,
                'rating' => rand(4, 5),
                'comment' => $reviews[$index],
            ]);
        }
    }

    private function createSecondTutor($location, $subjects)
    {
        // Create another tutor for variety
        $tutorUser2 = User::firstOrCreate([
            'email' => 'mihai.ionescu@example.com',
        ], [
            'first_name' => 'Mihai',
            'last_name' => 'Ionescu',
            'password' => Hash::make('password123'),
            'user_type' => 'tutor',
            'phone' => '+40722456789',
            'is_active' => true,
        ]);

        $tutor2 = Tutor::firstOrCreate([
            'user_id' => $tutorUser2->id,
        ], [
            'location_id' => $location->id,
            'bio' => 'Developer senior cu experiență în predarea programării. Specializat în Python, JavaScript și dezvoltare web modernă.',
            'hourly_rate' => 120.00,
            'offers_online' => true,
            'offers_in_person' => false, // Only online
            'experience' => 'Senior Developer la Microsoft România, instructor IT certificat.',
            'education' => 'Universitatea Politehnică București - Automatică și Calculatoare',
            'rating' => 5.0,
            'total_reviews' => 89,
            'total_lessons' => 245,
            'is_verified' => true,
            'is_featured' => false,
            'is_active' => true,
            'last_active' => now(),
        ]);

        // Attach programming subjects
        $tutor2->subjects()->syncWithoutDetaching([
            $subjects[2]->id => [
                'experience_description' => 'Programare Python și JavaScript pentru începători și avansați'
            ],
            $subjects[3]->id => [
                'experience_description' => 'Informatică pentru BAC și admitere facultate'
            ]
        ]);

        // Create availability (online only, different schedule)
        $availabilitySchedule2 = [
            'monday' => [
                ['start' => '18:00', 'end' => '22:00', 'type' => 'online']
            ],
            'tuesday' => [
                ['start' => '18:00', 'end' => '22:00', 'type' => 'online']
            ],
            'wednesday' => [
                ['start' => '18:00', 'end' => '22:00', 'type' => 'online']
            ],
            'thursday' => [
                ['start' => '18:00', 'end' => '22:00', 'type' => 'online']
            ],
            'saturday' => [
                ['start' => '09:00', 'end' => '17:00', 'type' => 'online']
            ],
            'sunday' => [
                ['start' => '10:00', 'end' => '16:00', 'type' => 'online']
            ]
        ];

        foreach ($availabilitySchedule2 as $day => $timeSlots) {
            foreach ($timeSlots as $slot) {
                Availability::create([
                    'tutor_id' => $tutorUser2->id,
                    'day_of_week' => $day,
                    'start_time' => $slot['start'],
                    'end_time' => $slot['end'],
                    'lesson_type' => $slot['type'],
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('✅ Second tutor created: ' . $tutorUser2->email . ' (ID: ' . $tutorUser2->id . ')');
    }
}
