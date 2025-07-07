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
                'education' => 'Cambridge University - English Literature, CELTA Certificate',
                'location_id' => 16, // Timișoara
                'subjects' => [6, 20, 21], // Limba Engleză, Pregătire BAC, Pregătire Admitere
                'rating' => 4.95,
                'total_reviews' => 156,
                'total_lessons' => 420,
                'is_verified' => true,
                'is_featured' => true,
            ],
            [
                'first_name' => 'Mihai',
                'last_name' => 'Constantinescu',
                'email' => 'mihai.constantinescu@tutoring.ro',
                'bio' => 'Doctorand în fizică cu pasiune pentru predare. Ajut studenții să înțeleagă fizica de la nivel liceal până la universitar.',
                'hourly_rate' => 85,
                'offers_online' => true,
                'offers_in_person' => true,
                'experience' => 'Doctorand în Fizică Teoretică, 6 ani experiență în predare.',
                'education' => 'Universitatea Alexandru Ioan Cuza Iași - Fizică',
                'location_id' => 17, // Iași
                'subjects' => [2, 20], // Fizică, Pregătire BAC
                'rating' => 4.7,
                'total_reviews' => 73,
                'total_lessons' => 189,
                'is_verified' => true,
                'is_featured' => false,
            ],
            [
                'first_name' => 'Elena',
                'last_name' => 'Dumitrescu',
                'email' => 'elena.dumitrescu@tutoring.ro',
                'bio' => 'Profesor de chimie cu experiență în pregătirea pentru olimpiade și bacalaureat. Metode interactive de predare.',
                'hourly_rate' => 70,
                'offers_online' => true,
                'offers_in_person' => true,
                'experience' => 'Profesor de chimie, medaliată la olimpiade naționale.',
                'education' => 'Universitatea București - Chimie',
                'location_id' => 19, // Constanța
                'subjects' => [3, 20], // Chimie, Pregătire BAC
                'rating' => 4.8,
                'total_reviews' => 94,
                'total_lessons' => 267,
                'is_verified' => true,
                'is_featured' => false,
            ],
            [
                'first_name' => 'Adrian',
                'last_name' => 'Marin',
                'email' => 'adrian.marin@tutoring.ro',
                'bio' => 'Full-stack developer și trainer IT. Predau dezvoltare web modernă și tehnologii cloud.',
                'hourly_rate' => 150,
                'offers_online' => true,
                'offers_in_person' => false,
                'experience' => 'Senior Developer la Microsoft România, instructor IT certificat.',
                'education' => 'Universitatea Politehnica București - Automatică și Calculatoare',
                'location_id' => 2, // București Sector 2
                'subjects' => [11, 12], // Programare, Baze de Date
                'rating' => 4.9,
                'total_reviews' => 112,
                'total_lessons' => 298,
                'is_verified' => true,
                'is_featured' => true,
            ],
            [
                'first_name' => 'Roxana',
                'last_name' => 'Vasile',
                'email' => 'roxana.vasile@tutoring.ro',
                'bio' => 'Profesor de biologie cu specializare în biologia moleculară. Pasionată de științele vieții.',
                'hourly_rate' => 65,
                'offers_online' => true,
                'offers_in_person' => true,
                'experience' => 'Profesor de biologie, cercetător în biologie moleculară.',
                'education' => 'Universitatea București - Biologie',
                'location_id' => 3, // București Sector 3
                'subjects' => [4, 20], // Biologie, Pregătire BAC
                'rating' => 4.6,
                'total_reviews' => 58,
                'total_lessons' => 134,
                'is_verified' => true,
                'is_featured' => false,
            ],
            [
                'first_name' => 'Cătălin',
                'last_name' => 'Popa',
                'email' => 'catalin.popa@tutoring.ro',
                'bio' => 'Profesor de franceză nativ, cu experiență în predarea limbii franceze pentru toate nivelurile.',
                'hourly_rate' => 80,
                'offers_online' => true,
                'offers_in_person' => true,
                'experience' => 'Profesor nativ de franceză, certificat DELF/DALF.',
                'education' => 'Sorbonne Paris - Littérature Française',
                'location_id' => 21, // Brașov
                'subjects' => [7], // Limba Franceză
                'rating' => 4.75,
                'total_reviews' => 67,
                'total_lessons' => 178,
                'is_verified' => true,
                'is_featured' => false,
            ]
        ];

        foreach ($tutors as $tutorData) {
            // Create user
            $user = User::create([
                'email' => $tutorData['email'],
                'password' => Hash::make('SecurePass123!'),
                'first_name' => $tutorData['first_name'],
                'last_name' => $tutorData['last_name'],
                'user_type' => 'tutor',
                'is_active' => true,
            ]);

            // Create tutor profile
            $tutor = Tutor::create([
                'user_id' => $user->id,
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
                'last_active' => now(),
            ]);

            // Attach subjects
            $tutor->subjects()->attach($tutorData['subjects']);

            // Create subscription
            Subscription::create([
                'tutor_id' => $user->id,
                'plan_type' => $tutorData['is_featured'] ? 'premium' : 'basic',
                'price' => $tutorData['is_featured'] ? 49.99 : 29.99,
                'status' => 'active',
                'started_at' => now()->subDays(rand(30, 365)),
                'expires_at' => now()->addDays(30),
            ]);
        }
    }
}
