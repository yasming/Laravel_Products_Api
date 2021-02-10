<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Product;
use App\Http\Resources\Product\ProductCollection;

class ProductControllerTest extends TestCase
{
    use DatabaseMigrations;
    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_it_should_be_able_to_list_all_products()
    {
        $allProducts = new ProductCollection(Product::all()->load('category'));
        $response    = $this->get('/api/products')
                            ->assertStatus(200);

        $this->assertEquals($allProducts->response()->getData(true)['data'],$response['products']);
        $this->assertEquals(count($response['products']), Product::all()->count());
    }
}
