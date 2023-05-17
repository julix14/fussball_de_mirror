<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaisonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $base = $this->getBase();

        //Read the Mandanten from the base.json file and create them in the database
        foreach ($base['Mandanten'] as $key => $value) {

            Mandant::factory()->create([
                'id' => $key,
                'name' => $value
            ]);
        }
    }
}
