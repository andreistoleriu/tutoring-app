<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ad;

class AdsSeeder extends Seeder
{
    public function run(): void
    {
        $ads = [
            [
                'title' => 'Cursuri Online de Programare',
                'description' => 'Învață programare cu instructori certificați. Cursuri interactive și proiecte practice. Începe astăzi!',
                'image_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=300&fit=crop',
                'click_url' => 'https://example.com/programming-courses',  // Using your actual field name
                'type' => 'banner',
                'placement' => 'header',  // Using your actual field name
                'targeting' => ['audience' => 'students'],  // Using your JSON field
                'priority' => 10,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
            ],
            [
                'title' => 'Platformă de Învățare Online',
                'description' => 'Accesează mii de cursuri și materiale educaționale. Proba gratuită de 7 zile.',
                'image_url' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400&h=300&fit=crop',
                'click_url' => 'https://example.com/learning-platform',
                'type' => 'card',
                'placement' => 'sidebar',
                'targeting' => ['audience' => 'all'],
                'priority' => 8,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addDays(60),
            ],
            [
                'title' => 'Meditații la Matematică',
                'description' => 'Profesori specializați în matematică pentru toate nivelurile. Rezultate garantate!',
                'image_url' => 'https://images.unsplash.com/photo-1596495578065-6e0763fa1178?w=400&h=300&fit=crop',
                'click_url' => 'https://example.com/math-tutoring',
                'type' => 'card',
                'placement' => 'feed',
                'targeting' => ['audience' => 'students'],
                'priority' => 9,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addDays(45),
            ],
            [
                'title' => 'Certificări IT Online',
                'description' => 'Obține certificări recunoscute în domeniul IT. Cursuri intensive și suport complet.',
                'image_url' => 'https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?w=400&h=300&fit=crop',
                'click_url' => 'https://example.com/it-certifications',
                'type' => 'banner',
                'placement' => 'header',
                'targeting' => ['audience' => 'tutors'],
                'priority' => 7,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addDays(90),
            ],
            [
                'title' => 'Cărți Educaționale - 30% Reducere',
                'description' => 'Descoperă cea mai mare colecție de cărți educaționale. Livrare gratuită la comenzi peste 100 RON.',
                'image_url' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=400&h=300&fit=crop',
                'click_url' => 'https://example.com/educational-books',
                'type' => 'card',
                'placement' => 'sidebar',
                'targeting' => ['audience' => 'trial_users'],
                'priority' => 6,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addDays(15),
            ],
            [
                'title' => 'Tabletă pentru Învățare',
                'description' => 'Perfectă pentru elevi și profesori. Preț special pentru comunitatea educațională.',
                'image_url' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400&h=300&fit=crop',
                'click_url' => 'https://example.com/learning-tablet',
                'type' => 'popup',
                'placement' => 'modal',
                'targeting' => ['audience' => 'all'],
                'priority' => 5,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
            ],
            [
                'title' => 'Workshop Online: Predarea Efectivă',
                'description' => 'Dezvoltă-ți abilitățile de predare cu experți în educație. Sesiune gratuită de introducere.',
                'image_url' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=400&h=300&fit=crop',
                'click_url' => 'https://example.com/teaching-workshop',
                'type' => 'banner',
                'placement' => 'footer',
                'targeting' => ['audience' => 'tutors'],
                'priority' => 8,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addDays(20),
            ],
            [
                'title' => 'Software de Gestiune Educațională',
                'description' => 'Organizează-ți lecțiile și studenții eficient. Proba gratuită de 30 de zile.',
                'image_url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=400&h=300&fit=crop',
                'click_url' => 'https://example.com/education-software',
                'type' => 'card',
                'placement' => 'sidebar',
                'targeting' => ['audience' => 'tutors'],
                'priority' => 7,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addDays(60),
            ],
        ];

        foreach ($ads as $adData) {
            Ad::firstOrCreate(
                [
                    'title' => $adData['title'],
                    'placement' => $adData['placement']  // Using actual field name
                ],
                $adData
            );
        }

        $this->command->info('✅ Created ' . count($ads) . ' sample ads');
    }
}
