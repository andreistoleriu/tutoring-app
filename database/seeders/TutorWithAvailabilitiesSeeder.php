<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Subject;
use App\Models\Location;
use App\Models\Availability;
use Carbon\Carbon;

class TutorWithAvailabilitiesSeeder extends Seeder
{
    /**
     * Run the database seeds to create test tutors with complete availability schedules.
     */
    public function run(): void
    {
        // Create or get subjects
        $subjects = collect([
            Subject::firstOrCreate(['slug' => 'matematica'], [
                'name' => 'Matematică',
                'description' => 'Matematică pentru toate nivelurile',
                'icon' => '📐',
                'is_active' => true
            ]),
            Subject::firstOrCreate(['slug' => 'engleza'], [
                'name' => 'Engleză',
                'description' => 'Limba engleză conversațională și academică',
                'icon' => '🇬🇧',
                'is_active' => true
            ]),
            Subject::firstOrCreate(['slug' => 'informatica'], [
                'name' => 'Informatică',
                'description' => 'Programare și informatică aplicată',
                'icon' => '💻',
                'is_active' => true
            ]),
            Subject::firstOrCreate(['slug' => 'fizica'], [
                'name' => 'Fizică',
                'description' => 'Fizică pentru liceu și bacalaureat',
                'icon' => '⚛️',
                'is_active' => true
            ]),
            Subject::firstOrCreate(['slug' => 'romana'], [
                'name' => 'Română',
                'description' => 'Limba și literatura română',
                'icon' => '📚',
                'is_active' => true
            ])
        ]);

        // Create or get locations
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
            ])
        ]);

        // Create multiple test tutors
        $tutorData = [
            [
                'email' => 'ana.popescu@example.com',
                'first_name' => 'Ana',
                'last_name' => 'Popescu',
                'phone' => '0721234567',
                'bio' => 'Profesor experimentat cu 8 ani de experiență în predarea matematicii și informaticii. Pasionată de educație și ajutarea studenților să-și atingă potențialul maxim.',
                'hourly_rate' => 75.00,
                'offers_online' => true,
                'offers_in_person' => true,
                'experience' => 'Licențiată în Matematică, Masterând în Informatică. 8 ani experiență în predare.',
                'education' => 'Universitatea București - Facultatea de Matematică și Informatică',
                'subjects' => ['matematica', 'informatica', 'fizica'],
                'location' => 'bucuresti',
                'rating' => 4.8,
                'reviews' => 24,
                'lessons' => 150,
                'is_featured' => true
            ],
            [
                'email' => 'mihai.ionescu@example.com',
                'first_name' => 'Mihai',
                'last_name' => 'Ionescu',
                'phone' => '0731234567',
                'bio' => 'Profesor de limba engleză cu certificare TEFL. Specializat în pregătirea pentru examene internaționale și conversație avansată.',
                'hourly_rate' => 65.00,
                'offers_online' => true,
                'offers_in_person' => false,
                'experience' => 'Certificat TEFL, 5 ani experiență în predarea limbii engleze pentru români.',
                'education' => 'Universitatea din București - Facultatea de Limbi Străine',
                'subjects' => ['engleza'],
                'location' => 'bucuresti',
                'rating' => 4.9,
                'reviews' => 18,
                'lessons' => 95,
                'is_featured' => false
            ],
            [
                'email' => 'elena.vasilescu@example.com',
                'first_name' => 'Elena',
                'last_name' => 'Vasilescu',
                'phone' => '0741234567',
                'bio' => 'Profesoară de română cu pasiune pentru literatura clasică și modernă. Ajut studenții să înțeleagă și să aprecieze limba română.',
                'hourly_rate' => 55.00,
                'offers_online' => true,
                'offers_in_person' => true,
                'experience' => 'Licențiată în Filologie, 6 ani experiență în predarea limbii române.',
                'education' => 'Universitatea Babeș-Bolyai - Facultatea de Litere',
                'subjects' => ['romana'],
                'location' => 'cluj-napoca',
                'rating' => 4.7,
                'reviews' => 12,
                'lessons' => 78,
                'is_featured' => false
            ]
        ];

        foreach ($tutorData as $data) {
            // Create tutor user
            $tutorUser = User::firstOrCreate(['email' => $data['email']], [
                'password' => bcrypt('password123'),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'user_type' => 'tutor',
                'is_active' => true,
            ]);

            // Get location
            $location = $locations->firstWhere('slug', $data['location']);

            // Create tutor profile
            $tutor = Tutor::firstOrCreate(['user_id' => $tutorUser->id], [
                'location_id' => $location->id,
                'bio' => $data['bio'],
                'hourly_rate' => $data['hourly_rate'],
                'offers_online' => $data['offers_online'],
                'offers_in_person' => $data['offers_in_person'],
                'experience' => $data['experience'],
                'education' => $data['education'],
                'rating' => $data['rating'],
                'total_reviews' => $data['reviews'],
                'total_lessons' => $data['lessons'],
                'is_verified' => true,
                'is_featured' => $data['is_featured'],
                'is_active' => true,
                'last_active' => now(),
            ]);

            // Attach subjects to tutor
            $tutorSubjects = $subjects->whereIn('slug', $data['subjects']);
            $tutor->subjects()->sync($tutorSubjects->pluck('id'));

            // Create comprehensive availability schedule
            $availabilities = $this->getAvailabilitySchedule($data['first_name']);

            // Clear existing availabilities
            Availability::where('tutor_id', $tutorUser->id)->delete();

            // Create new availabilities
            foreach ($availabilities as $availability) {
                Availability::create([
                    'tutor_id' => $tutorUser->id,
                    'day_of_week' => $availability['day_of_week'],
                    'start_time' => $availability['start_time'],
                    'end_time' => $availability['end_time'],
                    'lesson_type' => $availability['lesson_type'],
                    'is_active' => true,
                ]);
            }

            $this->command->info("✅ Created tutor: {$data['first_name']} {$data['last_name']} ({$data['email']})");
        }

        $this->command->info('');
        $this->command->info('🎉 All test tutors created successfully!');
        $this->command->info('🔑 Password for all tutors: password123');
        $this->command->info('');
        $this->command->info('📋 Test Tutors:');
        $this->command->info('1. Ana Popescu (ana.popescu@example.com) - Matematică, Informatică, Fizică');
        $this->command->info('2. Mihai Ionescu (mihai.ionescu@example.com) - Engleză (online only)');
        $this->command->info('3. Elena Vasilescu (elena.vasilescu@example.com) - Română');
        $this->command->info('');
        $this->command->info('📅 All tutors have comprehensive availability schedules');
    }

    /**
     * Get different availability schedules for different tutors
     */
    private function getAvailabilitySchedule($firstName): array
    {
        switch ($firstName) {
            case 'Ana':
                return [
                    // Monday - Full day availability
                    ['day_of_week' => 'monday', 'start_time' => '09:00', 'end_time' => '12:00', 'lesson_type' => 'both'],
                    ['day_of_week' => 'monday', 'start_time' => '14:00', 'end_time' => '18:00', 'lesson_type' => 'both'],
                    // Tuesday - Mixed online/in-person
                    ['day_of_week' => 'tuesday', 'start_time' => '10:00', 'end_time' => '13:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'tuesday', 'start_time' => '15:00', 'end_time' => '19:00', 'lesson_type' => 'both'],
                    // Wednesday - Balanced schedule
                    ['day_of_week' => 'wednesday', 'start_time' => '09:00', 'end_time' => '12:00', 'lesson_type' => 'both'],
                    ['day_of_week' => 'wednesday', 'start_time' => '14:00', 'end_time' => '17:00', 'lesson_type' => 'in_person'],
                    // Thursday - Online focus
                    ['day_of_week' => 'thursday', 'start_time' => '10:00', 'end_time' => '13:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'thursday', 'start_time' => '15:00', 'end_time' => '18:00', 'lesson_type' => 'both'],
                    // Friday - Light schedule
                    ['day_of_week' => 'friday', 'start_time' => '09:00', 'end_time' => '12:00', 'lesson_type' => 'both'],
                    ['day_of_week' => 'friday', 'start_time' => '14:00', 'end_time' => '16:00', 'lesson_type' => 'online'],
                    // Saturday - Weekend availability
                    ['day_of_week' => 'saturday', 'start_time' => '10:00', 'end_time' => '14:00', 'lesson_type' => 'both'],
                ];

            case 'Mihai':
                return [
                    // Online-only tutor with flexible schedule
                    ['day_of_week' => 'monday', 'start_time' => '08:00', 'end_time' => '11:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'monday', 'start_time' => '13:00', 'end_time' => '17:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'tuesday', 'start_time' => '09:00', 'end_time' => '12:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'tuesday', 'start_time' => '14:00', 'end_time' => '18:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'wednesday', 'start_time' => '08:00', 'end_time' => '11:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'wednesday', 'start_time' => '15:00', 'end_time' => '19:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'thursday', 'start_time' => '09:00', 'end_time' => '12:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'thursday', 'start_time' => '14:00', 'end_time' => '17:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'friday', 'start_time' => '08:00', 'end_time' => '11:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'friday', 'start_time' => '13:00', 'end_time' => '16:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'saturday', 'start_time' => '09:00', 'end_time' => '13:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'sunday', 'start_time' => '10:00', 'end_time' => '14:00', 'lesson_type' => 'online'],
                ];

            case 'Elena':
                return [
                    // Traditional schedule with both online and in-person
                    ['day_of_week' => 'monday', 'start_time' => '10:00', 'end_time' => '13:00', 'lesson_type' => 'both'],
                    ['day_of_week' => 'monday', 'start_time' => '15:00', 'end_time' => '18:00', 'lesson_type' => 'in_person'],
                    ['day_of_week' => 'tuesday', 'start_time' => '09:00', 'end_time' => '12:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'tuesday', 'start_time' => '14:00', 'end_time' => '17:00', 'lesson_type' => 'both'],
                    ['day_of_week' => 'wednesday', 'start_time' => '10:00', 'end_time' => '13:00', 'lesson_type' => 'both'],
                    ['day_of_week' => 'wednesday', 'start_time' => '15:00', 'end_time' => '17:00', 'lesson_type' => 'in_person'],
                    ['day_of_week' => 'thursday', 'start_time' => '09:00', 'end_time' => '12:00', 'lesson_type' => 'online'],
                    ['day_of_week' => 'thursday', 'start_time' => '14:00', 'end_time' => '16:00', 'lesson_type' => 'both'],
                    ['day_of_week' => 'friday', 'start_time' => '10:00', 'end_time' => '13:00', 'lesson_type' => 'both'],
                    ['day_of_week' => 'saturday', 'start_time' => '09:00', 'end_time' => '12:00', 'lesson_type' => 'both'],
                ];

            default:
                return [
                    // Default schedule
                    ['day_of_week' => 'monday', 'start_time' => '09:00', 'end_time' => '17:00', 'lesson_type' => 'both'],
                    ['day_of_week' => 'wednesday', 'start_time' => '09:00', 'end_time' => '17:00', 'lesson_type' => 'both'],
                    ['day_of_week' => 'friday', 'start_time' => '09:00', 'end_time' => '17:00', 'lesson_type' => 'both'],
                ];
        }
    }
}
