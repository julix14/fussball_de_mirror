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
        //Read the Mandanten from the base.json file and create them in the database
        foreach ($base['Mandanten'] as $key => $value) {
            $formattedKey = str_replace('_', '', $key);
            Mandant::factory()->create([
                'mandant_id' => $formattedKey,
                'name' => $value
            ]);
        }
    }
}
