<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\CompetitionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompetitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Initialize cURL
        $curl = curl_init();


        $baseJson = DB::table('base_data')
            ->where('is_current_year', true)
            ->first()
            ->data;

        $base = json_decode($baseJson, true);
        $alreadyCreatedCompetitionTypes = [];

        $competitionsToSave = [];

        foreach ($base['CompetitionTypes'] as $mandant => $competitionTypesByMandant) {
            foreach ($competitionTypesByMandant as $saison => $competitionTypesBySaison) {
                foreach ($competitionTypesBySaison as $key => $value) {
                    $formattedKey = $this->formatKey($key);

                    if (!in_array($formattedKey, $alreadyCreatedCompetitionTypes)) {
                        CompetitionType::factory()->create([
                            'competition_type_id' => $formattedKey,
                            'name' => $value
                        ]);
                        $alreadyCreatedCompetitionTypes[] = $formattedKey;

                    }

                    $mandantId = $this->formatKey($mandant);
                    $saisonId = $this->formatKey($saison);

                    $url = "https://www.fussball.de/wam_kinds_{$mandantId}_{$saisonId}_{$formattedKey}.json";

                    // Set cURL options
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

                    // Execute the cURL request
                    $data = curl_exec($curl);

                    // Check for errors
                    if ($data === false) {
                        echo "Error occurred while fetching data from: {$url}\n";
                        continue;
                    }

                    $competitionsToSave[] = [
                        'mandant_id' => $mandantId,
                        'saison_id' => $saisonId,
                        'competition_type_id' => $formattedKey,
                        'data' => $data,
                    ];

                    echo "Created Competition for {$mandantId}, {$saisonId}, {$formattedKey}\n";

                }
            }
        }

        // Save all competitions at once
        Competition::insert($competitionsToSave);

        curl_close($curl);

    }

    private function formatKey($key): string
    {
        return str_replace('_', '', $key);
    }
}
