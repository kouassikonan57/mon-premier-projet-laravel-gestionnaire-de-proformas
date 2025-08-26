<?php

namespace Database\Seeders;
use App\Models\Filiale;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FilialeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $filiales = [
            ['nom' => 'DDCS', 'code' => 'DDCS'],
            ['nom' => 'YADI CAR CENTER', 'code' => 'YCC'],
            ['nom' => 'YDIA CONSTRUCTION', 'code' => 'YDC'],
            ['nom' => 'VROOM', 'code' => 'VRM'],
        ];

        foreach ($filiales as $filiale) {
            Filiale::create($filiale);
        }
}
}
