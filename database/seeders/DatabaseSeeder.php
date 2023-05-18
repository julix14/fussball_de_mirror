<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\BaseData;
use App\Models\Mandant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         User::factory(10)->create();

         User::factory()->create([
             'name' => 'Test User',
             'email' => 'test@example.com',
         ]);

         $baseJsonUrl = 'https://www.fussball.de/wam_base.json';
         $baseJson = file_get_contents($baseJsonUrl);
         $base = json_decode($baseJson, true);

         BaseData::factory()->create([
             'year' => $base['currentSaison'],
             'data' => $baseJson,
             'is_current_year' => str_ends_with($base['currentSaison'], date('y')),
         ]);

        $this->call([
            MandantenSeeder::class,
            SaisonsSeeder::class,
            CompetitionSeeder::class,
            LeagueSeeder::class,
        ]);
    }
}
