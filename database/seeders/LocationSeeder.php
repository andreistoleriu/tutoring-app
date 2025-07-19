<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            // Major Cities & Counties
            ['county' => 'București', 'city' => 'Sector 1'],
            ['county' => 'București', 'city' => 'Sector 2'],
            ['county' => 'București', 'city' => 'Sector 3'],
            ['county' => 'București', 'city' => 'Sector 4'],
            ['county' => 'București', 'city' => 'Sector 5'],
            ['county' => 'București', 'city' => 'Sector 6'],

            // Ilfov (around Bucharest)
            ['county' => 'Ilfov', 'city' => 'Voluntari'],
            ['county' => 'Ilfov', 'city' => 'Popești-Leordeni'],
            ['county' => 'Ilfov', 'city' => 'Pantelimon'],
            ['county' => 'Ilfov', 'city' => 'Otopeni'],
            ['county' => 'Ilfov', 'city' => 'Chitila'],
            ['county' => 'Ilfov', 'city' => 'Buftea'],

            // Major Cities
            ['county' => 'Cluj', 'city' => 'Cluj-Napoca'],
            ['county' => 'Cluj', 'city' => 'Turda'],
            ['county' => 'Cluj', 'city' => 'Dej'],

            ['county' => 'Timiș', 'city' => 'Timișoara'],
            ['county' => 'Timiș', 'city' => 'Lugoj'],

            ['county' => 'Iași', 'city' => 'Iași'],
            ['county' => 'Iași', 'city' => 'Pașcani'],

            ['county' => 'Constanța', 'city' => 'Constanța'],
            ['county' => 'Constanța', 'city' => 'Mangalia'],
            ['county' => 'Constanța', 'city' => 'Năvodari'],

            ['county' => 'Brașov', 'city' => 'Brașov'],
            ['county' => 'Brașov', 'city' => 'Săcele'],
            ['county' => 'Brașov', 'city' => 'Codlea'],

            ['county' => 'Dolj', 'city' => 'Craiova'],
            ['county' => 'Dolj', 'city' => 'Băilești'],

            ['county' => 'Galați', 'city' => 'Galați'],
            ['county' => 'Galați', 'city' => 'Tecuci'],

            ['county' => 'Prahova', 'city' => 'Ploiești'],
            ['county' => 'Prahova', 'city' => 'Câmpina'],
            ['county' => 'Prahova', 'city' => 'Băicoi'],

            ['county' => 'Bihor', 'city' => 'Oradea'],
            ['county' => 'Bihor', 'city' => 'Salonta'],

            ['county' => 'Arad', 'city' => 'Arad'],
            ['county' => 'Arad', 'city' => 'Lipova'],

            ['county' => 'Sibiu', 'city' => 'Sibiu'],
            ['county' => 'Sibiu', 'city' => 'Mediaș'],

            ['county' => 'Bacău', 'city' => 'Bacău'],
            ['county' => 'Bacău', 'city' => 'Onești'],

            ['county' => 'Mureș', 'city' => 'Târgu Mureș'],
            ['county' => 'Mureș', 'city' => 'Sighișoara'],

            ['county' => 'Argeș', 'city' => 'Pitești'],
            ['county' => 'Argeș', 'city' => 'Câmpulung'],

            ['county' => 'Dâmbovița', 'city' => 'Târgoviște'],
            ['county' => 'Dâmbovița', 'city' => 'Moreni'],

            ['county' => 'Suceava', 'city' => 'Suceava'],
            ['county' => 'Suceava', 'city' => 'Câmpulung Moldovenesc'],

            ['county' => 'Neamț', 'city' => 'Piatra Neamț'],
            ['county' => 'Neamț', 'city' => 'Roman'],

            ['county' => 'Maramureș', 'city' => 'Baia Mare'],
            ['county' => 'Maramureș', 'city' => 'Sighetu Marmației'],

            ['county' => 'Satu Mare', 'city' => 'Satu Mare'],
            ['county' => 'Satu Mare', 'city' => 'Carei'],

            ['county' => 'Bistrița-Năsăud', 'city' => 'Bistrița'],

            ['county' => 'Alba', 'city' => 'Alba Iulia'],
            ['county' => 'Alba', 'city' => 'Sebeș'],

            ['county' => 'Hunedoara', 'city' => 'Deva'],
            ['county' => 'Hunedoara', 'city' => 'Hunedoara'],
            ['county' => 'Hunedoara', 'city' => 'Petroșani'],

            ['county' => 'Caraș-Severin', 'city' => 'Reșița'],
            ['county' => 'Caraș-Severin', 'city' => 'Caransebeș'],

            ['county' => 'Gorj', 'city' => 'Târgu Jiu'],

            ['county' => 'Vâlcea', 'city' => 'Râmnicu Vâlcea'],
            ['county' => 'Vâlcea', 'city' => 'Drăgășani'],

            ['county' => 'Olt', 'city' => 'Slatina'],
            ['county' => 'Olt', 'city' => 'Caracal'],

            ['county' => 'Teleorman', 'city' => 'Alexandria'],
            ['county' => 'Teleorman', 'city' => 'Rosiori de Vede'],

            ['county' => 'Giurgiu', 'city' => 'Giurgiu'],

            ['county' => 'Călărași', 'city' => 'Călărași'],

            ['county' => 'Ialomița', 'city' => 'Slobozia'],

            ['county' => 'Brăila', 'city' => 'Brăila'],

            ['county' => 'Buzău', 'city' => 'Buzău'],
            ['county' => 'Buzău', 'city' => 'Râmnicu Sărat'],

            ['county' => 'Vrancea', 'city' => 'Focșani'],
            ['county' => 'Vrancea', 'city' => 'Adjud'],

            ['county' => 'Vaslui', 'city' => 'Vaslui'],
            ['county' => 'Vaslui', 'city' => 'Bârlad'],
            ['county' => 'Vaslui', 'city' => 'Huși'],

            ['county' => 'Botoșani', 'city' => 'Botoșani'],
            ['county' => 'Botoșani', 'city' => 'Dorohoi'],

            ['county' => 'Sălaj', 'city' => 'Zalău'],

            ['county' => 'Mehedinți', 'city' => 'Drobeta-Turnu Severin'],

            ['county' => 'Tulcea', 'city' => 'Tulcea'],

            ['county' => 'Covasna', 'city' => 'Sfântu Gheorghe'],

            ['county' => 'Harghita', 'city' => 'Miercurea Ciuc'],
            ['county' => 'Harghita', 'city' => 'Odorheiu Secuiesc'],
        ];

        foreach ($locations as $location) {
            $slug = Str::slug($location['city'] . '-' . $location['county']);

            // Use firstOrCreate to prevent duplicate slug errors
            Location::firstOrCreate(
                ['slug' => $slug], // Find by slug
                [
                    'county' => $location['county'],
                    'city' => $location['city'],
                    'slug' => $slug,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Locations seeded successfully!');
    }
}
