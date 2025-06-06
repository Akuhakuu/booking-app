<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemSeeder extends Seeder
{
    public function run()
    {
        DB::table('items')->insert([
            [
                'id' => 1,
                'name' => 'Tenda Dome 4 Orang',
                'rental_price' => 50000.00,
                'description' => 'Tenda berkapasitas 4 orang dengan bahan tahan air, cocok untuk camping keluarga.',
                'category_id' => 1,
                'brand_id' => 1,
                'stock' => 10,
                'img' => 'item_1746978753_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Sleeping Bag Polar',
                'rental_price' => 25000.00,
                'description' => 'Sleeping bag berbahan polar, menjaga tubuh tetap hangat di malam hari yang dingin.',
                'category_id' => 1,
                'brand_id' => 18,
                'stock' => 10,
                'img' => 'item_1746978844_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'name' => 'Matras Gulung Waterproof',
                'rental_price' => 10000.00,
                'description' => 'Matras ringan, mudah digulung, dan tahan air untuk kenyamanan tidur di alam.',
                'category_id' => 1,
                'brand_id' => 3,
                'stock' => 10,
                'img' => 'item_1746978878_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'name' => 'Carrier 70L',
                'rental_price' => 80000.00,
                'description' => 'Tas gunung berkapasitas besar, cocok untuk perjalanan panjang dan membawa banyak barang.',
                'category_id' => 1,
                'brand_id' => 5,
                'stock' => 10,
                'img' => 'item_1746978912_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'name' => 'Lampu Camping LED',
                'rental_price' => 8000.00,
                'description' => 'Lampu LED hemat energi, ideal untuk penerangan tenda dan area sekitar perkemahan.',
                'category_id' => 1,
                'brand_id' => 6,
                'stock' => 10,
                'img' => 'item_1746978943_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'name' => 'Kompor Gas Portable',
                'rental_price' => 20000.00,
                'description' => 'Kompor praktis untuk memasak di luar ruangan, menggunakan gas butane.',
                'category_id' => 2,
                'brand_id' => 2,
                'stock' => 10,
                'img' => 'item_1746978979_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 7,
                'name' => 'Botol Air Lipat 1L',
                'rental_price' => 5000.00,
                'description' => 'Botol air fleksibel dan bisa dilipat, cocok untuk menghemat ruang di tas.',
                'category_id' => 2,
                'brand_id' => 8,
                'stock' => 10,
                'img' => 'item_1746979009_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 8,
                'name' => 'Nesting Cookware Set',
                'rental_price' => 25000.00,
                'description' => 'Set peralatan masak ringkas dan ringan, terdiri dari panci, wajan, dan tutup.',
                'category_id' => 2,
                'brand_id' => 7,
                'stock' => 10,
                'img' => 'item_1746979085_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 9,
                'name' => 'Teko Lipat Silikon',
                'rental_price' => 10000.00,
                'description' => 'Teko lipat berbahan silikon food grade, cocok untuk menyeduh teh/kopi di alam.',
                'category_id' => 2,
                'brand_id' => 5,
                'stock' => 10,
                'img' => 'item_1746979120_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 10,
                'name' => 'Kotak Makan Stainless',
                'rental_price' => 12000.00,
                'description' => 'Kotak makan tahan lama dan insulasi baik, menjaga makanan tetap hangat.',
                'category_id' => 2,
                'brand_id' => 9,
                'stock' => 10,
                'img' => 'item_1746979150_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 11,
                'name' => 'Jaket Waterproof',
                'rental_price' => 35000.00,
                'description' => 'Jaket anti air dan tahan angin, cocok untuk pendakian atau hujan ringan',
                'category_id' => 3, // Pastikan ID kategori ini ada di tabel categories
                'brand_id' => 3,    // Pastikan ID brand ini ada di tabel brands
                'stock' => 10,
                'img' => 'item_1746979195_cekop.jpg', // Sesuaikan nama file jika perlu, atau set null
                'status' => 'available',
                'created_at' => Carbon::create(2025, 5, 11, 15, 59, 55),
                'updated_at' => Carbon::create(2025, 5, 11, 15, 59, 55),
            ],
            [
                'id' => 12,
                'name' => 'Celana Gunung Quickdry',
                'rental_price' => 30000.00,
                'description' => 'Celana dengan bahan cepat kering, nyaman digunakan saat tracking berat',
                'category_id' => 3,
                'brand_id' => 10,
                'stock' => 10,
                'img' => 'item_1746979228_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::create(2025, 5, 11, 16, 0, 28),
                'updated_at' => Carbon::create(2025, 5, 11, 16, 0, 28),
            ],
            [
                'id' => 13,
                'name' => 'Helm Panjat Tebing',
                'rental_price' => 35000.00,
                'description' => 'Helm ringan dan kokoh untuk keselamatan dalam aktivitas panjat tebing',
                'category_id' => 4,
                'brand_id' => 4,
                'stock' => 10,
                'img' => 'item_1746979292_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::create(2025, 5, 11, 16, 1, 32),
                'updated_at' => Carbon::create(2025, 5, 11, 16, 1, 32),
            ],
            [
                'id' => 14,
                'name' => 'Sarung Tangan Thermal',
                'rental_price' => 15000.00,
                'description' => 'Sarung tangan hangat untuk perlindungan dari cuaca dingin ekstrem',
                'category_id' => 3,
                'brand_id' => 11,
                'stock' => 10,
                'img' => 'item_1746980086_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::create(2025, 5, 11, 16, 14, 46),
                'updated_at' => Carbon::create(2025, 5, 11, 16, 14, 46),
            ],
            [
                'id' => 15,
                'name' => 'Topi Trekking Anti-UV',
                'rental_price' => 10000.00,
                'description' => 'Topi ringan dengan perlindungan UV, menjaga kepala dari panas matahari',
                'category_id' => 3,
                'brand_id' => 12,
                'stock' => 10,
                'img' => 'item_1746980115_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::create(2025, 5, 11, 16, 15, 15),
                'updated_at' => Carbon::create(2025, 5, 11, 16, 15, 15),
            ],
            [
                'id' => 16,
                'name' => 'Kacamata Outdoor Polarized',
                'rental_price' => 20000.00,
                'description' => 'Kacamata dengan lensa polarized, mengurangi silau dan melindungi mata dari UV',
                'category_id' => 3,
                'brand_id' => 13,
                'stock' => 10,
                'img' => 'item_1746980146_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::create(2025, 5, 11, 16, 15, 46),
                'updated_at' => Carbon::create(2025, 5, 11, 16, 15, 46),
            ],
            [
                'id' => 17,
                'name' => 'Carabiner D-Ring',
                'rental_price' => 7000.00,
                'description' => 'Carabiner berbahan aluminium dengan sistem pengunci, cocok untuk rigging',
                'category_id' => 4,
                'brand_id' => 14,
                'stock' => 10,
                'img' => 'item_1746980181_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::create(2025, 5, 11, 16, 16, 21),
                'updated_at' => Carbon::create(2025, 5, 11, 16, 16, 21),
            ],
            [
                'id' => 18,
                'name' => 'Harness Panjat Dewasa',
                'rental_price' => 40000.00,
                'description' => 'Harness yang nyaman dan aman, digunakan saat memanjat tebing atau rappelling',
                'category_id' => 4,
                'brand_id' => 15,
                'stock' => 10,
                'img' => 'item_1746980207_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::create(2025, 5, 11, 16, 16, 47),
                'updated_at' => Carbon::create(2025, 5, 11, 16, 16, 47),
            ],
            [
                'id' => 19,
                'name' => 'Webbing 5 Meter',
                'rental_price' => 10000.00,
                'description' => 'Tali pipih kuat, ideal untuk membuat anchor atau slackline sederhana',
                'category_id' => 4,
                'brand_id' => 16,
                'stock' => 10,
                'img' => 'item_1746980242_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::create(2025, 5, 11, 16, 17, 22),
                'updated_at' => Carbon::create(2025, 5, 11, 16, 17, 22),
            ],
            [
                'id' => 20,
                'name' => 'Sepatu Climbing',
                'rental_price' => 45000.00,
                'description' => 'Sepatu panjat dengan grip tinggi dan desain ergonomis untuk dinding vertikal',
                'category_id' => 4,
                'brand_id' => 17,
                'stock' => 8, // Stock berbeda untuk item ini
                'img' => 'item_1746980270_cekop.jpg',
                'status' => 'available',
                'created_at' => Carbon::create(2025, 5, 11, 16, 17, 50),
                'updated_at' => Carbon::create(2025, 5, 12, 14, 37, 6), // Updated_at berbeda
            ]
        ]);
    }
}
