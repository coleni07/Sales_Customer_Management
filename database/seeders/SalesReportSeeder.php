<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Region;
use App\Models\Representative;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Regions =====
        $luzon    = Region::create(['name' => 'Luzon',    'color' => '#14B8A6', 'monthly_target' => 14000]);
        $visayas  = Region::create(['name' => 'Visayas',  'color' => '#3B82F6', 'monthly_target' => 8500]);
        $mindanao = Region::create(['name' => 'Mindanao', 'color' => '#F5B301', 'monthly_target' => 5500]);

        // ===== Products =====
        $products = [
            ['name' => 'Wireless Headphones', 'category' => 'Audio',       'price' => 42.00, 'monthly_target' => 5200],
            ['name' => 'Phone Case',          'category' => 'Accessories', 'price' => 40.00, 'monthly_target' => 5100],
            ['name' => 'Fast Charger',        'category' => 'Power',       'price' => 50.00, 'monthly_target' => 3600],
            ['name' => 'Earbuds',             'category' => 'Audio',       'price' => 46.00, 'monthly_target' => 3300],
            ['name' => 'USB-C Cable',         'category' => 'Power',       'price' => 47.00, 'monthly_target' => 2200],
            ['name' => 'Bluetooth Speaker',   'category' => 'Audio',       'price' => 48.00, 'monthly_target' => 2300],
            ['name' => 'Screen Protector',    'category' => 'Accessories', 'price' => 10.00, 'monthly_target' => 1500],
            ['name' => 'Power Bank',          'category' => 'Power',       'price' => 55.00, 'monthly_target' => 2000],
            ['name' => 'Phone Ring Holder',   'category' => 'Accessories', 'price' => 10.00, 'monthly_target' => 900],
            ['name' => 'Wireless Charger Pad','category' => 'Power',       'price' => 60.00, 'monthly_target' => 1600],
        ];
        $productIds = collect($products)->map(fn ($p) => Product::create($p)->id)->values();

        // ===== Representatives (one home rep per region) =====
        $maria = Representative::create(['name' => 'Maria Santos', 'region_id' => $luzon->id,    'monthly_quota' => 12000]);
        $jose  = Representative::create(['name' => 'Jose Reyes',   'region_id' => $visayas->id,  'monthly_quota' => 8000]);
        $ana   = Representative::create(['name' => 'Ana Cruz',     'region_id' => $mindanao->id, 'monthly_quota' => 5500]);

        $repIds = [$maria->id, $jose->id, $ana->id];
        $homeRepByRegion = [
            $luzon->id    => $maria->id,
            $visayas->id  => $jose->id,
            $mindanao->id => $ana->id,
        ];

        // Weighted region pool: Luzon sells most, Mindanao least — matches the
        // "Luzon on track / Mindanao critical" story already built into the dashboard.
        $regionPool = array_merge(
            array_fill(0, 5, $luzon->id),
            array_fill(0, 3, $visayas->id),
            array_fill(0, 2, $mindanao->id),
        );

        $productPrices = collect($products)->pluck('price', 'name')->values();
        $productIdList = $productIds->all();

        // ===== Generate ~200 days of transactions (covers the 6M chart range) =====
        $rows = [];
        $startDate = Carbon::now()->subDays(200);

        for ($day = 0; $day <= 200; $day++) {
            $date = $startDate->copy()->addDays($day);
            $transactionsToday = rand(3, 8);

            for ($t = 0; $t < $transactionsToday; $t++) {
                $regionId = $regionPool[array_rand($regionPool)];
                $productId = $productIdList[array_rand($productIdList)];
                $product = Product::find($productId);

                // 80% of the time the region's home rep makes the sale, 20% another rep assists
                $repId = (rand(1, 100) <= 80)
                    ? $homeRepByRegion[$regionId]
                    : $repIds[array_rand($repIds)];

                $quantity = rand(1, 6);
                $amount = round($product->price * $quantity * (rand(90, 110) / 100), 2);

                $rows[] = [
                    'product_id'        => $productId,
                    'region_id'         => $regionId,
                    'representative_id' => $repId,
                    'quantity'          => $quantity,
                    'amount'            => $amount,
                    'sale_date'         => $date->format('Y-m-d'),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            // Insert in chunks so we're not building one giant array in memory
            if (count($rows) >= 500) {
                DB::table('sales')->insert($rows);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            DB::table('sales')->insert($rows);
        }
    }
}
