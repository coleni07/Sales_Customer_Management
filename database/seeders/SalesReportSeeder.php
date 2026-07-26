<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Region;
use App\Models\Representative;
use Illuminate\Database\Seeder;

class SalesReportSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Regions =====
        $luzon = Region::create(['name' => 'Luzon', 'color' => '#14B8A6', 'monthly_target' => 14000]);
        $visayas = Region::create(['name' => 'Visayas', 'color' => '#3B82F6', 'monthly_target' => 8500]);
        $mindanao = Region::create(['name' => 'Mindanao', 'color' => '#F5B301', 'monthly_target' => 5500]);

        // ===== Products =====
        $products = [
            ['name' => 'Wireless Headphones', 'category' => 'Audio', 'price' => 42.00, 'monthly_target' => 5200],
            ['name' => 'Phone Case', 'category' => 'Accessories', 'price' => 40.00, 'monthly_target' => 5100],
            ['name' => 'Fast Charger', 'category' => 'Power', 'price' => 50.00, 'monthly_target' => 3600],
            ['name' => 'Earbuds', 'category' => 'Audio', 'price' => 46.00, 'monthly_target' => 3300],
            ['name' => 'USB-C Cable', 'category' => 'Power', 'price' => 47.00, 'monthly_target' => 2200],
            ['name' => 'Bluetooth Speaker', 'category' => 'Audio', 'price' => 48.00, 'monthly_target' => 2300],
            ['name' => 'Screen Protector', 'category' => 'Accessories', 'price' => 10.00, 'monthly_target' => 1500],
            ['name' => 'Power Bank', 'category' => 'Power', 'price' => 55.00, 'monthly_target' => 2000],
            ['name' => 'Phone Ring Holder', 'category' => 'Accessories', 'price' => 10.00, 'monthly_target' => 900],
            ['name' => 'Wireless Charger Pad', 'category' => 'Power', 'price' => 60.00, 'monthly_target' => 1600],
        ];

        foreach ($products as $p) {
            Product::create($p);
        }

        // ===== Representatives (one home rep per region) =====
        Representative::create(['name' => 'Maria Santos', 'region_id' => $luzon->id, 'monthly_quota' => 12000]);
        Representative::create(['name' => 'Jose Reyes', 'region_id' => $visayas->id, 'monthly_quota' => 8000]);
        Representative::create(['name' => 'Ana Cruz', 'region_id' => $mindanao->id, 'monthly_quota' => 5500]);
    }
}