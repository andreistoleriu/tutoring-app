<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;
use Illuminate\Support\Str;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Mathematics & Sciences
            [
                'name' => 'Matematică',
                'description' => 'Algebră, geometrie, analiză matematică, statistică',
                'icon' => 'calculator'
            ],
            [
                'name' => 'Fizică',
                'description' => 'Mecanică, termodinamică, electricitate, optică',
                'icon' => 'zap'
            ],
            [
                'name' => 'Chimie',
                'description' => 'Chimie generală, organică, anorganică',
                'icon' => 'flask'
            ],
            [
                'name' => 'Biologie',
                'description' => 'Biologia celulei, genetică, ecologie, anatomie',
                'icon' => 'leaf'
            ],

            // Languages
            [
                'name' => 'Limba Română',
                'description' => 'Gramatică, literatură, redactare',
                'icon' => 'book-open'
            ],
            [
                'name' => 'Limba Engleză',
                'description' => 'Gramatică, conversație, literatură engleză',
                'icon' => 'globe'
            ],
            [
                'name' => 'Limba Franceză',
                'description' => 'Gramatică, conversație, literatură franceză',
                'icon' => 'globe'
            ],
            [
                'name' => 'Limba Germană',
                'description' => 'Gramatică, conversație, literatură germană',
                'icon' => 'globe'
            ],

            // Computer Science
            [
                'name' => 'Informatică',
                'description' => 'Algoritmi, structuri de date, programare',
                'icon' => 'monitor'
            ],
            [
                'name' => 'Programare',
                'description' => 'Python, JavaScript, Java, C++',
                'icon' => 'code'
            ],
            [
                'name' => 'Baze de Date',
                'description' => 'SQL, MySQL, PostgreSQL, design baze de date',
                'icon' => 'database'
            ],

            // Social Sciences
            [
                'name' => 'Istorie',
                'description' => 'Istoria României, istoria universală',
                'icon' => 'book'
            ],
            [
                'name' => 'Geografie',
                'description' => 'Geografia României, geografia continentelor',
                'icon' => 'map'
            ],
            [
                'name' => 'Filosofie',
                'description' => 'Logică, etică, filosofie contemporană',
                'icon' => 'lightbulb'
            ],

            // Economics & Business
            [
                'name' => 'Economie',
                'description' => 'Microeconomie, macroeconomie, economie aplicată',
                'icon' => 'trending-up'
            ],
            [
                'name' => 'Contabilitate',
                'description' => 'Contabilitate financiară, gestiune, audit',
                'icon' => 'calculator'
            ],

            // Arts & Music
            [
                'name' => 'Desen & Pictură',
                'description' => 'Desen academic, pictură în ulei, acuarelă',
                'icon' => 'palette'
            ],
            [
                'name' => 'Muzică',
                'description' => 'Pian, chitară, vioară, teoria muzicii',
                'icon' => 'music'
            ],

            // Exam Preparation
            [
                'name' => 'Pregătire BAC',
                'description' => 'Pregătire pentru bacalaureat - toate materiile',
                'icon' => 'graduation-cap'
            ],
            [
                'name' => 'Pregătire Admitere',
                'description' => 'Pregătire pentru admiterea la facultate',
                'icon' => 'target'
            ],
            [
                'name' => 'Evaluare Națională',
                'description' => 'Pregătire pentru evaluarea națională clasa a VIII-a',
                'icon' => 'clipboard-check'
            ],

            // Other Skills
            [
                'name' => 'Șah',
                'description' => 'Strategii de șah, tactici, deschideri',
                'icon' => 'grid-3x3'
            ],
            [
                'name' => 'Conducere Auto',
                'description' => 'Pregătire pentru permisul de conducere',
                'icon' => 'car'
            ],
        ];

        foreach ($subjects as $subject) {
            $slug = Str::slug($subject['name']);

            // Use firstOrCreate to prevent duplicate slug errors
            Subject::firstOrCreate(
                ['slug' => $slug], // Find by slug
                [
                    'name' => $subject['name'],
                    'slug' => $slug,
                    'description' => $subject['description'],
                    'icon' => $subject['icon'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Subjects seeded successfully!');
    }
}
