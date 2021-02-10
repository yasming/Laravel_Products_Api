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

    public function test_it_should_be_able_to_detele_a_product()
    {
        $product = Product::first();
        $response = $this->delete('/api/products/1')
                        ->assertStatus(204);
        
        $this->assertEquals($response->getData(true),[]);
        $this->assertEquals(Product::find(1), null);
        $this->assertDatabaseMissing('products', $product->toArray());
    }

    public function test_it_should_not_be_able_to_detele_a_product()
    {
        $lastProductId = Product::max('id') + 1;
        $this->delete('/api/products/'.$lastProductId)
             ->assertStatus(404);   
    }
}
