<?php

namespace Database\Seeders;

use App\Models\BaseData;
use App\Models\Mandant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MandantenSeeder extends Seeder
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

        $alreadyCreatedMandanten = Mandanten::all()
            ->pluck('mandant_id')
            ->map(fn ($item) => FormatHelper::class->formatKey($item))
            ->toArray();
        //Read the Mandanten from the base.json file and create them in the database
        foreach ($base['Mandanten'] as $key => $value) {
            $formattedKey = str_replace('_', '', $key);
            if (!in_array($formattedKey, $alreadyCreatedMandanten)) {
                Mandant::factory()->create([
                    'mandant_id' => $formattedKey,
                    'name' => $value
                ]);
                $alreadyCreatedMandanten[] = $formattedKey;
            }


        }
    }
}
