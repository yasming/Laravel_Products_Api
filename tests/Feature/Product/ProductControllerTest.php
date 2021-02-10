<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Product;
use App\Http\Resources\Product\ProductCollection;
use App\Models\Category;

class ProductControllerTest extends TestCase
{
    use DatabaseMigrations;
    public function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();
        $this->seed();
    }

    public function test_it_should_be_able_to_list_all_products()
    {
        $allProducts = new ProductCollection(Product::all()->load('category'));
        $response    = $this->get(route('api.products.index'))
                            ->assertStatus(200);

        $this->assertEquals($allProducts->response()->getData(true)['data'],$response['products']);
        $this->assertEquals(count($response['products']), Product::all()->count());
    }

    public function test_it_should_be_able_to_detele_a_product()
    {
        $product = Product::first();
        $response = $this->delete(route('api.products.destroy',1))
                        ->assertStatus(204);
        
        $this->assertEquals($response->getData(true),[]);
        $this->assertEquals(Product::find(1), null);
        $this->assertDatabaseMissing('products', $product->toArray());
    }

    public function test_it_should_not_be_able_to_detele_a_product()
    {
        $lastProductIdPlusOne = Product::max('id') + 1;
        $this->delete(route('api.products.destroy',$lastProductIdPlusOne))
             ->assertStatus(404);   
    }

     /** 
     * 
     * @dataProvider getRequiredFields
     * 
     * @param string $field
     * @param string $fieldName
    */

    public function test_it_should_validate_required_fields_to_create_or_update_products(string $field, string $fieldName)
    {
        $this->post(route('api.products.store'), $this->getRequiredFields([
            $field => null,
        ]))->assertSessionHasErrors([
            $field => __('validation.required', [
                'attribute' => $fieldName,
            ])
        ])->assertStatus(302);
    }

    public function getRequiredFields() : array
    {
        return [
            ['name', 'name'],
            ['price', 'price'],
            ['category_id', 'category id']
        ];
    }

    public function test_it_should_validate_name_field_to_create_or_update_products()
    {
        $this->post(route('api.products.store'), [
            'name' => 123
        ])->assertSessionHasErrors([
            'name' => __('validation.string', [
                'attribute' => 'name',
            ])
        ])->assertStatus(302);
    
        Product::first()->update(['name' => 'product']);
        $this->post(route('api.products.store'), [
            'name' => 'product'
        ])->assertSessionHasErrors([
            'name' => __('validation.unique', [
                'attribute' => 'name',
            ])
        ])->assertStatus(302);
    }

    public function test_it_should_validate_price_field_to_create_or_update_products()
    {
        $this->post(route('api.products.store'), [
            'price' => 'string'
        ])->assertSessionHasErrors([
            'price' => __('validation.numeric', [
                'attribute' => 'price',
            ])
        ])->assertStatus(302);

        $this->post(route('api.products.store'), [
            'price' => 0
        ])->assertSessionHasErrors([
            'price' => __('validation.min.numeric', [
                'attribute' => 'price',
                'min'       => 1
            ])
        ])->assertStatus(302);
    }

    public function test_it_should_validate_category_id_field_to_create_or_update_products()
    {
        $this->post(route('api.products.store'), [
            'category_id' => 'string'
        ])->assertSessionHasErrors([
            'category_id' => __('validation.integer', [
                'attribute' => 'category id',
            ])
        ])->assertStatus(302);

        $lastCategoryIdPlusOne =  Category::max('id') + 1;

        $this->post(route('api.products.store'), [
            'category_id' =>  $lastCategoryIdPlusOne
        ])->assertSessionHasErrors([
            'category_id' => __('validation.exists', [
                'attribute' => 'category id',
            ])
        ])->assertStatus(302);
    }

    public function test_it_should_be_able_to_create_a_product()
    {
    
        $response = $this->post(route('api.products.store'), 
                        $this->getFieldsToCreateProduct()                
                     )->assertStatus(201);  

        $response->assertExactJson([
                      'name'        => 'teste',
                      'price'       => 10,
                      'category_id' => Category::first()->id,
                      'created_at'  => $response['created_at'],
                      'updated_at'  => $response['updated_at'],
                      'id'          => $response['id']
                ]);

        $this->assertDatabaseHas('products', [
                'name'        => 'teste',
                'price'       => 10,
                'category_id' => Category::first()->id,
            ]);
    }

    private function getFieldsToCreateProduct()
    {
        return [
                    'name'        => 'teste',
                    'price'       => 10,
                    'category_id' => Category::first()->id
        ]       ;
        
    }
}
