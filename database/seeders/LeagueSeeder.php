<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Competition;
use App\Models\GameClass;
use App\Models\League;
use App\Models\TeamKind;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LeagueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $curl = curl_init();
        $leaguesToSave = [];

        $competitions = Competition::all();
        foreach ($competitions as $competition) {
            $data = json_decode($competition->data, true);

            $teamKinds = $data['Mannschaftsart'];
            $gameClasses = $data['Spielklasse'];
            $areas = $data['Gebiet'];

            $createdTeamKinds = TeamKind::all()
                ->pluck('team_kind_id')
                ->map(function ($item) {
                    return $this->formatKey($item);
                })
                ->toArray();
            $createdGameClasses = GameClass::all()
                ->pluck('game_class_id')
                ->map(function ($item) {
                    return $this->formatKey($item);
                })
                ->toArray();

            $createdAreas = Area::all()
                ->pluck('area_id')
                ->map(function ($item) {
                    return $this->formatKey($item);
                })
                ->toArray();

            foreach ($teamKinds as $teamKindKey => $teamKindValue) {

                if (!in_array($teamKindKey, $createdTeamKinds)) {
                    TeamKind::factory()->create([
                        'team_kind_id' => $this->formatKey($teamKindKey),
                        'name' => $teamKindValue
                    ]);
                    $createdTeamKinds[] = $teamKindKey;

                }
                $teamKindId = $this->formatKey($teamKindKey);
                $gameClassesByTeamKind = $gameClasses[$teamKindId];
                foreach ($gameClassesByTeamKind as $gameClassKey => $gameClassValue) {
                    if (!in_array($gameClassKey, $createdGameClasses)) {
                        GameClass::factory()->create([
                            'game_class_id' => $this->formatKey($gameClassKey),
                            'name' => $gameClassValue,
                        ]);
                        $createdGameClasses[] = $gameClassKey;
                    }
                    $gameClassId = $this->formatKey($gameClassKey);

                    $areasByGameClass = $areas[$teamKindId][$gameClassId];
                    foreach ($areasByGameClass as $areaKey => $areaValue) {
                        if (!in_array($areaKey, $createdAreas)) {
                            Area::factory()->create([
                                'area_id' => $this->formatKey($areaKey),
                                'name' => $areaValue,
                            ]);
                            $createdAreas[] = $areaKey;
                        }
                        $mandantId = $this->formatKey($competition->mandant_id);
                        $saisonId = $this->formatKey($competition->saison_id);
                        $competitionTypeId = $this->formatKey($competition->competition_type_id);

                        $areaId = $this->formatKey($areaKey);

                        $url = "https://www.fussball.de/wam_competitions_{$mandantId}_{$saisonId}_{$competitionTypeId}_{$teamKindId}_{$gameClassId}_{$areaId}.json";

                        // Set cURL options
                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

                        $data = curl_exec($curl);

                        $leaguesToSave[] = [
                            'game_class_id' => $gameClassId,
                            'team_kind_id' => $teamKindId,
                            'competition_id' => $this->formatKey($competition->competition_id),
                            'area_id' => $areaId,
                            'data' => $data
                        ];
                        echo "Created league for {$teamKindValue} {$gameClassValue} {$areaValue}\n";

                    }

                }

            }

        }

        League::insert($leaguesToSave);
    }
    private function formatKey($key): string
    {
        if (Str::startsWith($key, '_')) {
            return str_replace('_', '', $key);
        }
        return '_'.$key;
    }
}
