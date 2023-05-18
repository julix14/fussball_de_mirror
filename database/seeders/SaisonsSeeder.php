<?php

namespace Database\Seeders;

use App\Models\Mandant;
use App\Models\Saison;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaisonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baseJson = DB::table('base_data')
            ->where('is_current_year', true)
            ->first()
            ->data;

        $base = json_decode($baseJson, true);
        $alreadyCreatedSaisons = Saison::all()
            ->pluck('saison_id')
            ->map(fn ($item) => FormatHelper::formatKey($item))
            ->toArray();
        //Read the Saisons from the base.json file and create them in the database
        foreach ($base['Saisons'] as $saisons) {
            foreach ($saisons as $key => $value) {
                $formattedKey = str_replace('_', '', $key);
                if (in_array($formattedKey, $alreadyCreatedSaisons)) {
                    Saison::factory()->create([
                        'saison_id' => $formattedKey,
                        'name' => $value
                    ]);
                    $alreadyCreatedSaisons[] = $formattedKey;
                }

            }
        }
    }
}
