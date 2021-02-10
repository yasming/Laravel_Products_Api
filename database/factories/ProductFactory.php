<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name'        => $this->faker->name,
            'price'       => $this->faker->numberBetween(1, 10),
            'category_id' => $this->faker->numberBetween(1, 10)
        ];
    }
}
