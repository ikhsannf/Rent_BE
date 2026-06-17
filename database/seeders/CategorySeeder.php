<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kamera & Foto', 'icon' => 'camera', 'color' => '#E53E3E'],
            ['name' => 'Drone', 'icon' => 'drone', 'color' => '#3182CE'],
            ['name' => 'Alat Camping', 'icon' => 'tent', 'color' => '#38A169'],
            ['name' => 'Olahraga Air', 'icon' => 'water', 'color' => '#0BC5EA'],
            ['name' => 'Musik & Audio', 'icon' => 'music', 'color' => '#805AD5'],
            ['name' => 'Gaming & Console', 'icon' => 'gamepad', 'color' => '#DD6B20'],
            ['name' => 'Alat Listrik', 'icon' => 'tool', 'color' => '#718096'],
            ['name' => 'Transportasi', 'icon' => 'bike', 'color' => '#D69E2E'],
        ];

        foreach ($categories as $index => $category) {
            Category::create([
                'name'        => $category['name'],
                'slug'        => Str::slug($category['name']),
                'icon'        => $category['icon'],
                'color'       => $category['color'],
                'description' => "Kategori untuk {$category['name']}",
                'is_active'   => true,
                'sort_order'  => $index,
            ]);
        }
    }
}
