<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BrandSeeder extends Seeder
{
    public function run()
    {
        $brands = [
            ['id' => 1, 'name' => 'Consina'],
            ['id' => 2, 'name' => 'Hi-Cook'],
            ['id' => 3, 'name' => 'Eiger'],
            ['id' => 4, 'name' => 'Petzl'],
            ['id' => 5, 'name' => 'Naturehike'],
            ['id' => 6, 'name' => 'Sunrei'],
            ['id' => 7, 'name' => 'Fire-Maple'],
            ['id' => 8, 'name' => 'Quechua'],
            ['id' => 9, 'name' => 'Stanley'],
            ['id' => 10, 'name' => 'The North Face'],
            ['id' => 11, 'name' => 'Outdoor Research'],
            ['id' => 12, 'name' => 'Columbia'],
            ['id' => 13, 'name' => 'Decathlon'],
            ['id' => 14, 'name' => 'Black Diamond'],
            ['id' => 15, 'name' => 'Mammut'],
            ['id' => 16, 'name' => 'Singing Rock'],
            ['id' => 17, 'name' => 'La Sportiva'],
            ['id' => 18, 'name' => 'Rei'],
        ];

        foreach ($brands as &$brand) {
            $brand['created_at'] = Carbon::now();
            $brand['updated_at'] = Carbon::now();
        }

        DB::table('brands')->insert($brands);
    }
}
