<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Stan',
                'description' => 'Stambene jedinice u zgradama, pogodne za kupovinu ili izdavanje.',
            ],
            [
                'name' => 'Kuca',
                'description' => 'Porodicne kuce sa dvoristem ili dodatnim pomocnim prostorom.',
            ],
            [
                'name' => 'Plac',
                'description' => 'Gradjevinsko i poljoprivredno zemljiste.',
            ],
            [
                'name' => 'Poslovni prostor',
                'description' => 'Lokali, kancelarije i drugi prostori za poslovnu namenu.',
            ],
        ];

        foreach ($categories as $category) {
            Category::factory()->create($category);
        }

        Category::factory(2)->create();
    }
}
