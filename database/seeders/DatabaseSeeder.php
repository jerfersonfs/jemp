<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin JEMP',
            'email' => 'admin@jemp.com',
            'password' => Hash::make('senha123'),
        ]);

        Warehouse::create([
            'warehouse_name' => 'Service Base',
            'warehouse_description' => 'Cargas Gerais',
        ]);

        Product::create([
            'sku' => 'PBR-TEST-001',
            'product_name' => 'Palete Madeira Padrão',
            'category' => 'PBR',
            'width_mm' => 1000,
            'length_mm' => 1200,
            'material' => 'Madeira',
            'cost_price' => 35.50,
        ]);
    }
}
