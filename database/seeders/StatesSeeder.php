<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StatesSeeder extends Seeder
{
    public function run(): void
    {
        // Get the U.S. country ID
        $country = DB::table('countries')->where('iso2', 'US')->first();

        if (! $country) {
            $this->command->error('❌ Country with iso2=US not found. Please seed countries first.');

            return;
        }

        $states = [
            ['name' => 'Alabama', 'iso2' => 'AL', 'code' => 'AL', 'capital' => 'Montgomery', 'latitude' => '32.377716', 'longitude' => '-86.300568'],
            ['name' => 'Alaska', 'iso2' => 'AK', 'code' => 'AK', 'capital' => 'Juneau', 'latitude' => '58.301598', 'longitude' => '-134.420212'],
            ['name' => 'Arizona', 'iso2' => 'AZ', 'code' => 'AZ', 'capital' => 'Phoenix', 'latitude' => '33.448143', 'longitude' => '-112.096962'],
            ['name' => 'Arkansas', 'iso2' => 'AR', 'code' => 'AR', 'capital' => 'Little Rock', 'latitude' => '34.746613', 'longitude' => '-92.288986'],
            ['name' => 'California', 'iso2' => 'CA', 'code' => 'CA', 'capital' => 'Sacramento', 'latitude' => '38.576668', 'longitude' => '-121.493629'],
            ['name' => 'Colorado', 'iso2' => 'CO', 'code' => 'CO', 'capital' => 'Denver', 'latitude' => '39.739227', 'longitude' => '-104.984856'],
            ['name' => 'Connecticut', 'iso2' => 'CT', 'code' => 'CT', 'capital' => 'Hartford', 'latitude' => '41.764046', 'longitude' => '-72.682198'],
            ['name' => 'Delaware', 'iso2' => 'DE', 'code' => 'DE', 'capital' => 'Dover', 'latitude' => '39.157307', 'longitude' => '-75.519722'],
            ['name' => 'Florida', 'iso2' => 'FL', 'code' => 'FL', 'capital' => 'Tallahassee', 'latitude' => '30.438118', 'longitude' => '-84.281296'],
            ['name' => 'Georgia', 'iso2' => 'GA', 'code' => 'GA', 'capital' => 'Atlanta', 'latitude' => '33.749027', 'longitude' => '-84.388229'],
            ['name' => 'Hawaii', 'iso2' => 'HI', 'code' => 'HI', 'capital' => 'Honolulu', 'latitude' => '21.307442', 'longitude' => '-157.857376'],
            ['name' => 'Idaho', 'iso2' => 'ID', 'code' => 'ID', 'capital' => 'Boise', 'latitude' => '43.617775', 'longitude' => '-116.199722'],
            ['name' => 'Illinois', 'iso2' => 'IL', 'code' => 'IL', 'capital' => 'Springfield', 'latitude' => '39.798363', 'longitude' => '-89.654961'],
            ['name' => 'Indiana', 'iso2' => 'IN', 'code' => 'IN', 'capital' => 'Indianapolis', 'latitude' => '39.768623', 'longitude' => '-86.162643'],
            ['name' => 'Iowa', 'iso2' => 'IA', 'code' => 'IA', 'capital' => 'Des Moines', 'latitude' => '41.591087', 'longitude' => '-93.603729'],
            ['name' => 'Kansas', 'iso2' => 'KS', 'code' => 'KS', 'capital' => 'Topeka', 'latitude' => '39.048191', 'longitude' => '-95.677956'],
            ['name' => 'Kentucky', 'iso2' => 'KY', 'code' => 'KY', 'capital' => 'Frankfort', 'latitude' => '38.186722', 'longitude' => '-84.875374'],
            ['name' => 'Louisiana', 'iso2' => 'LA', 'code' => 'LA', 'capital' => 'Baton Rouge', 'latitude' => '30.457069', 'longitude' => '-91.187393'],
            ['name' => 'Maine', 'iso2' => 'ME', 'code' => 'ME', 'capital' => 'Augusta', 'latitude' => '44.307167', 'longitude' => '-69.781693'],
            ['name' => 'Maryland', 'iso2' => 'MD', 'code' => 'MD', 'capital' => 'Annapolis', 'latitude' => '38.978764', 'longitude' => '-76.490936'],
            ['name' => 'Massachusetts', 'iso2' => 'MA', 'code' => 'MA', 'capital' => 'Boston', 'latitude' => '42.358162', 'longitude' => '-71.063698'],
            ['name' => 'Michigan', 'iso2' => 'MI', 'code' => 'MI', 'capital' => 'Lansing', 'latitude' => '42.733635', 'longitude' => '-84.555328'],
            ['name' => 'Minnesota', 'iso2' => 'MN', 'code' => 'MN', 'capital' => 'Saint Paul', 'latitude' => '44.955097', 'longitude' => '-93.102211'],
            ['name' => 'Mississippi', 'iso2' => 'MS', 'code' => 'MS', 'capital' => 'Jackson', 'latitude' => '32.303848', 'longitude' => '-90.182106'],
            ['name' => 'Missouri', 'iso2' => 'MO', 'code' => 'MO', 'capital' => 'Jefferson City', 'latitude' => '38.579201', 'longitude' => '-92.172935'],
            ['name' => 'Montana', 'iso2' => 'MT', 'code' => 'MT', 'capital' => 'Helena', 'latitude' => '46.585709', 'longitude' => '-112.018417'],
            ['name' => 'Nebraska', 'iso2' => 'NE', 'code' => 'NE', 'capital' => 'Lincoln', 'latitude' => '40.808075', 'longitude' => '-96.699654'],
            ['name' => 'Nevada', 'iso2' => 'NV', 'code' => 'NV', 'capital' => 'Carson City', 'latitude' => '39.163914', 'longitude' => '-119.766121'],
            ['name' => 'New Hampshire', 'iso2' => 'NH', 'code' => 'NH', 'capital' => 'Concord', 'latitude' => '43.206898', 'longitude' => '-71.538055'],
            ['name' => 'New Jersey', 'iso2' => 'NJ', 'code' => 'NJ', 'capital' => 'Trenton', 'latitude' => '40.220596', 'longitude' => '-74.769913'],
            ['name' => 'New Mexico', 'iso2' => 'NM', 'code' => 'NM', 'capital' => 'Santa Fe', 'latitude' => '35.68224', 'longitude' => '-105.939728'],
            ['name' => 'New York', 'iso2' => 'NY', 'code' => 'NY', 'capital' => 'Albany', 'latitude' => '42.652843', 'longitude' => '-73.757874'],
            ['name' => 'North Carolina', 'iso2' => 'NC', 'code' => 'NC', 'capital' => 'Raleigh', 'latitude' => '35.78043', 'longitude' => '-78.639099'],
            ['name' => 'Texas', 'iso2' => 'TX', 'code' => 'TX', 'capital' => 'Austin', 'latitude' => '30.266666', 'longitude' => '-97.73333'],
        ];

        foreach ($states as $state) {
            DB::table('states')->insert([
                'id' => Str::uuid(),
                'country_id' => $country->id,
                'name' => $state['name'],
                'iso2' => $state['iso2'],
                'code' => $state['code'],
                'capital' => $state['capital'],
                'latitude' => $state['latitude'],
                'longitude' => $state['longitude'],
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ States for United States seeded successfully!');
    }
}
