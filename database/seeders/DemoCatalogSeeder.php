<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\{Product,ProductVariant};

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        for ($i=1; $i<=5; $i++) {
            $p = Product::create([
                'name' => 'Sample Product '.$i,
                'slug' => 'sample-product-'.$i,
                'description' => 'Demo product '.$i,
                'price' => rand(1000, 2999) / 100,
                'is_active' => true,
            ]);
            $v = $p->variants()->create([
                'sku' => Str::upper(Str::random(8)),
                'option_summary' => null,
                'price' => null,
                'weight_oz' => 12,
                'length_in' => 10,
                'width_in' => 7,
                'height_in' => 2,
            ]);
            $v->inventory()->create(['qty_on_hand'=>10,'qty_reserved'=>0,'bin_location'=>'A1-01-01']);
        }
    }
}
