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
        $luzon = Region::create(['name' => 'Luzon', 'color' => '#14B8A6', 'monthly_target' => 180000.00]);
        $visayas = Region::create(['name' => 'Visayas', 'color' => '#3B82F6', 'monthly_target' => 110000.00]);
        $mindanao = Region::create(['name' => 'Mindanao', 'color' => '#F5B301', 'monthly_target' => 70000.00]);

        // ===== Products (Realistic PHP Pricing) =====
        $products = [
            ['name' => 'Wireless Headphones', 'category' => 'Audio', 'price' => 2500.00, 'monthly_target' => 100000],
            ['name' => 'Phone Case', 'category' => 'Accessories', 'price' => 350.00, 'monthly_target' => 10000],
            ['name' => 'Fast Charger', 'category' => 'Power', 'price' => 800.00, 'monthly_target' => 20000],
            ['name' => 'Earbuds', 'category' => 'Audio', 'price' => 1500.00, 'monthly_target' => 25000],
            ['name' => 'USB-C Cable', 'category' => 'Power', 'price' => 250.00, 'monthly_target' => 6000],
            ['name' => 'Bluetooth Speaker', 'category' => 'Audio', 'price' => 3000.00, 'monthly_target' => 70000],
            ['name' => 'Screen Protector', 'category' => 'Accessories', 'price' => 150.00, 'monthly_target' => 4500],
            ['name' => 'Power Bank', 'category' => 'Power', 'price' => 1200.00, 'monthly_target' => 30000],
            ['name' => 'Phone Ring Holder', 'category' => 'Accessories', 'price' => 100.00, 'monthly_target' => 2500],
            ['name' => 'Wireless Charger Pad', 'category' => 'Power', 'price' => 1800.00, 'monthly_target' => 50000],
        ];

        foreach ($products as $p) {
            Product::create($p);
        }

        // ===== Representatives (one home rep per region) =====
        Representative::create(['name' => 'Maria Santos', 'region_id' => $luzon->id, 'monthly_quota' => 120000.00]);
        Representative::create(['name' => 'Jose Reyes', 'region_id' => $visayas->id, 'monthly_quota' => 80000.00]);
        Representative::create(['name' => 'Ana Cruz', 'region_id' => $mindanao->id, 'monthly_quota' => 50000.00]);
    }
}