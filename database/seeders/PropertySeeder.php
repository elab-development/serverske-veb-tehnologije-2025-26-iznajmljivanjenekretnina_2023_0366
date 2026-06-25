<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apartment = Category::where('name', 'Stan')->first();
        $house = Category::where('name', 'Kuca')->first();
        $land = Category::where('name', 'Plac')->first();
        $business = Category::where('name', 'Poslovni prostor')->first();

        $properties = [
            [
                'category_id' => $apartment->id,
                'title' => 'Svetao trosoban stan kod Hrama',
                'description' => 'Komforan stan u mirnoj ulici, sa odlicnim rasporedom i puno prirodnog svetla.',
                'price' => 245000,
                'city' => 'Beograd',
                'address' => 'Skerliceva 12',
                'area' => 82,
                'rooms' => 3,
                'bathrooms' => 1,
                'floor' => '3',
                'total_floors' => 6,
                'year_built' => 2008,
                'listing_type' => Property::LISTING_TYPE_SALE,
                'status' => Property::STATUS_ACTIVE,
                'is_featured' => true,
                'published_at' => now()->subDays(12),
            ],
            [
                'category_id' => $house->id,
                'title' => 'Porodicna kuca sa dvoristem',
                'description' => 'Kuca u urednom naselju, sa garazom, pomocnim objektom i prostranim dvoristem.',
                'price' => 185000,
                'city' => 'Novi Sad',
                'address' => 'Fruskogorska 24',
                'area' => 145,
                'rooms' => 5,
                'bathrooms' => 2,
                'floor' => null,
                'total_floors' => 2,
                'year_built' => 1998,
                'listing_type' => Property::LISTING_TYPE_SALE,
                'status' => Property::STATUS_ACTIVE,
                'is_featured' => true,
                'published_at' => now()->subDays(8),
            ],
            [
                'category_id' => $apartment->id,
                'title' => 'Moderan stan za izdavanje na Dorcolu',
                'description' => 'Namesten dvosoban stan u blizini centra, pogodan za dugorocni zakup.',
                'price' => 950,
                'city' => 'Beograd',
                'address' => 'Cara Dusana 43',
                'area' => 54,
                'rooms' => 2,
                'bathrooms' => 1,
                'floor' => '2',
                'total_floors' => 5,
                'year_built' => 2016,
                'listing_type' => Property::LISTING_TYPE_RENT,
                'status' => Property::STATUS_ACTIVE,
                'is_featured' => false,
                'published_at' => now()->subDays(4),
            ],
            [
                'category_id' => $land->id,
                'title' => 'Gradjevinski plac pored glavnog puta',
                'description' => 'Ravan plac sa pristupom putu i prikljuccima u neposrednoj blizini.',
                'price' => 72000,
                'city' => 'Indjija',
                'address' => 'Industrijska zona bb',
                'area' => 1200,
                'rooms' => null,
                'bathrooms' => null,
                'floor' => null,
                'total_floors' => null,
                'year_built' => null,
                'listing_type' => Property::LISTING_TYPE_SALE,
                'status' => Property::STATUS_ACTIVE,
                'is_featured' => false,
                'published_at' => now()->subDays(18),
            ],
            [
                'category_id' => $business->id,
                'title' => 'Lokal u prometnoj ulici',
                'description' => 'Useljiv lokal sa velikim izlogom, pogodan za maloprodaju ili usluznu delatnost.',
                'price' => 1500,
                'city' => 'Nis',
                'address' => 'Obrenoviceva 9',
                'area' => 68,
                'rooms' => null,
                'bathrooms' => 1,
                'floor' => 'ground',
                'total_floors' => 1,
                'year_built' => 2010,
                'listing_type' => Property::LISTING_TYPE_RENT,
                'status' => Property::STATUS_ACTIVE,
                'is_featured' => false,
                'published_at' => now()->subDays(6),
            ],
        ];

        foreach ($properties as $property) {
            Property::factory()->create($property);
        }

        $categories = Category::all();

        Property::factory(8)
            ->active()
            ->state(fn () => [
                'category_id' => $categories->random()->id,
            ])
            ->create();
    }
}
