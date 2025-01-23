<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run()
    {
        $citiesByCountry = [
            'France' => ['Paris', 'Lyon', 'Marseille', 'Bordeaux', 'Lille', 'Toulouse', 'Nantes'],
            'Belgique' => ['Bruxelles', 'Anvers', 'Gand', 'Liège', 'Namur'],
            'Suisse' => ['Genève', 'Zurich', 'Lausanne', 'Berne', 'Bâle'],
            'Canada' => ['Montréal', 'Québec', 'Ottawa', 'Toronto', 'Vancouver'],
            'Sénégal' => ['Dakar', 'Saint-Louis', 'Thiès', 'Rufisque'],
            'Côte d\'Ivoire' => ['Abidjan', 'Yamoussoukro', 'Bouaké', 'Korhogo']
        ];

        foreach ($citiesByCountry as $countryName => $cities) {
            $country = Country::where('name', $countryName)->first();
            if ($country) {
                foreach ($cities as $cityName) {
                    City::create([
                        'name' => $cityName,
                        'country_id' => $country->id
                    ]);
                }
            }
        }
    }
}