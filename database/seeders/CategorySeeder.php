<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['id' => 1, 'name' => 'Camping & Hiking'],
            ['id' => 2, 'name' => 'Cooking & Kitchen'],
            ['id' => 3, 'name' => 'Clothing & Accessories'],
            ['id' => 4, 'name' => 'Climbing & Adventure Gear'],
        ];

        foreach ($categories as &$category) {
            $category['created_at'] = Carbon::now();
            $category['updated_at'] = Carbon::now();
        }

        DB::table('categories')->insert($categories);
    }
}
