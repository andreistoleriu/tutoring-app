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
            [
                'name' => 'Limba Spaniolă',
                'description' => 'Gramatică, conversație, literatura spaniolă',
                'icon' => 'globe'
            ],

            // Computer Science & Technology
            [
                'name' => 'Informatică',
                'description' => 'Programare, algoritmi, structuri de date',
                'icon' => 'computer'
            ],
            [
                'name' => 'Programare',
                'description' => 'Python, Java, C++, JavaScript, web development',
                'icon' => 'code'
            ],
            [
                'name' => 'Baze de Date',
                'description' => 'SQL, MySQL, PostgreSQL, design de baze de date',
                'icon' => 'database'
            ],

            // Social Sciences & Humanities
            [
                'name' => 'Istorie',
                'description' => 'Istoria României, istoria universală, istoria modernă',
                'icon' => 'clock'
            ],
            [
                'name' => 'Geografie',
                'description' => 'Geografia României, geografia fizică, geografia economică',
                'icon' => 'map'
            ],
            [
                'name' => 'Filozofie',
                'description' => 'Istoria filozofiei, logică, etică',
                'icon' => 'lightbulb'
            ],

            // Economics & Business
            [
                'name' => 'Economie',
                'description' => 'Microeconomie, macroeconomie, economia României',
                'icon' => 'trending-up'
            ],
            [
                'name' => 'Contabilitate',
                'description' => 'Contabilitate financiară, contabilitate de gestiune',
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
            Subject::create([
                'name' => $subject['name'],
                'slug' => Str::slug($subject['name']),
                'description' => $subject['description'],
                'icon' => $subject['icon'],
                'is_active' => true,
            ]);
        }
    }
}
