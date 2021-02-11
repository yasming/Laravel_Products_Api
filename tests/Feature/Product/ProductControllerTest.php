<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Product;
use App\Http\Resources\Product\ProductCollection;
use App\Models\Category;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ProductControllerTest extends TestCase
{
    use DatabaseMigrations;
    private $product;
    public function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();
        $this->seed();
        $this->product = Product::first();
    }

    public function test_it_should_be_able_to_list_all_products()
    {
        $allProducts = new ProductCollection(Product::all()->load('category'));
        $response    = $this->get(route('api.products.index'))
                            ->assertStatus(HttpResponse::HTTP_OK);

        $this->assertEquals($allProducts->response()->getData(true)['data'],$response['products']);
        $this->assertEquals(count($response['products']), Product::all()->count());
    }

    public function test_it_should_be_able_to_detele_a_product()
    {
        $product = Product::first();
        $response = $this->delete(route('api.products.destroy',1))
                        ->assertStatus(HttpResponse::HTTP_NO_CONTENT);
        
        $this->assertEquals($response->getData(true),[]);
        $this->assertEquals(Product::find(1), null);
        $this->assertDatabaseMissing('products', $product->toArray());
    }

    public function test_it_should_not_be_able_to_detele_a_product()
    {
        $lastProductIdPlusOne = Product::max('id') + 1;
        $this->delete(route('api.products.destroy',$lastProductIdPlusOne))
             ->assertStatus(HttpResponse::HTTP_NOT_FOUND);   
    }

    /** 
     * 
     * @dataProvider getRequiredFields
     * 
     * @param string $field
     * @param string $fieldName
    */

    public function test_it_should_validate_required_fields_to_create_a_product(string $field, string $fieldName)
    {
        $this->post(route('api.products.store'), $this->getRequiredFields([
            $field => null,
        ]))->assertSessionHasErrors([
            $field => __('validation.required', [
                'attribute' => $fieldName,
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);
    }

    /** 
     * 
     * @dataProvider getRequiredFields
     * 
     * @param string $field
     * @param string $fieldName
    */

    public function test_it_should_validate_required_fields_to_update_a_product(string $field, string $fieldName)
    {
        $this->put(route('api.products.update', 1), $this->getRequiredFields([
            $field => null,
        ]))->assertSessionHasErrors([
            $field => __('validation.required', [
                'attribute' => $fieldName,
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);
    }

    public function getRequiredFields() : array
    {
        return [
            ['name', 'name'],
            ['price', 'price'],
            ['category_id', 'category id']
        ];
    }

    /** 
     * 
     * @dataProvider getStringFields
     * 
     * @param string $field
     * @param string $fieldName
    */

    public function test_it_should_validate_string_fields_to_create_products(string $field, string $fieldName)
    {
        $this->post(route('api.products.store'), [
            $field => 123
        ])->assertSessionHasErrors([
            $field => __('validation.string', [
                'attribute' => $fieldName,
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);
    
        Product::first()->update(['name' => 'product']);
        $this->post(route('api.products.store'), [
            'name' => 'product'
        ])->assertSessionHasErrors([
            'name' => __('validation.unique', [
                'attribute' => 'name',
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);

    }

    public function getStringFields() : array
    {
        return [
            ['name', 'name'],
        ];
    }

    /** 
     * 
     * @dataProvider getStringFields
     * 
     * @param string $field
     * @param string $fieldName
    */

    public function test_it_should_validate_string_fields_to_update_products(string $field, string $fieldName)
    {
        $this->put(route('api.products.update', 1), [
            $field => 123
        ])->assertSessionHasErrors([
            $field => __('validation.string', [
                'attribute' => $fieldName,
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);
    }

    /** 
     * 
     * @dataProvider getNumericFields
     * 
     * @param string $field
     * @param string $fieldName
    */

    public function test_it_should_validate_numeric_fields_to_create_products(string $field, string $fieldName)
    {
        $this->post(route('api.products.store'), [
            $field => 'string'
        ])->assertSessionHasErrors([
            $field => __('validation.numeric', [
                'attribute' => $fieldName,
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);

        $this->post(route('api.products.store'), [
            $field => 0
        ])->assertSessionHasErrors([
            $field => __('validation.min.numeric', [
                'attribute' => $fieldName,
                'min'       => 1
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);
    }

    public function getNumericFields() : array
    {
        return [
            ['price', 'price'],
        ];
    }

    /** 
     * 
     * @dataProvider getNumericFields
     * 
     * @param string $field
     * @param string $fieldName
    */

    public function test_it_should_validate_numeric_fields_to_update_products(string $field, string $fieldName)
    {
        $this->put(route('api.products.update', 1), [
            $field => 'string'
        ])->assertSessionHasErrors([
            $field => __('validation.numeric', [
                'attribute' => $fieldName,
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);

        $this->put(route('api.products.update', 1), [
            $field => 0
        ])->assertSessionHasErrors([
            $field => __('validation.min.numeric', [
                'attribute' => $fieldName,
                'min'       => 1
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);
    }

    /** 
     * 
     * @dataProvider getCategoryField
     * 
     * @param string $field
     * @param string $fieldName
    */

    public function test_it_should_validate_category_id_field_to_create_products(string $field, string $fieldName)
    {
        $this->post(route('api.products.store'), [
            $field => 'string'
        ])->assertSessionHasErrors([
            $field => __('validation.integer', [
                'attribute' => $fieldName,
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);

        $lastCategoryIdPlusOne =  Category::max('id') + 1;

        $this->post(route('api.products.store'), [
            $field =>  $lastCategoryIdPlusOne
        ])->assertSessionHasErrors([
            $field => __('validation.exists', [
                'attribute' => $fieldName,
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);
    }

    public function getCategoryField() : array
    {
        return [
            ['category_id', 'category id'],
        ];
    }

    /** 
     * 
     * @dataProvider getCategoryField
     * 
     * @param string $field
     * @param string $fieldName
    */

    public function test_it_should_validate_category_id_field_to_update_products(string $field, string $fieldName)
    {
        $this->put(route('api.products.update', 1), [
            $field => 'string'
        ])->assertSessionHasErrors([
            $field => __('validation.integer', [
                'attribute' => $fieldName,
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);

        $lastCategoryIdPlusOne =  Category::max('id') + 1;

        $this->put(route('api.products.update', 1), [
            $field =>  $lastCategoryIdPlusOne
        ])->assertSessionHasErrors([
            $field => __('validation.exists', [
                'attribute' => $fieldName,
            ])
        ])->assertStatus(HttpResponse::HTTP_FOUND);
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
        ];
        
    }

    public function test_it_should_not_be_able_to_update_a_product()
    {
        $lastProductIdPlusOne = Product::max('id') + 1;
        $this->put(route('api.products.update', $lastProductIdPlusOne), 
                        $this->getFieldsToUpdateProduct()                
                     )->assertStatus(HttpResponse::HTTP_NOT_FOUND);
    }

    public function test_it_should_be_able_to_update_a_product()
    {
        $response = $this->put(route('api.products.update', 1), 
                        $this->getFieldsToUpdateProduct()                
                     )->assertStatus(HttpResponse::HTTP_OK);  

        $response->assertExactJson([
                      'name'        => $this->product->name,
                      'price'       => 11,
                      'category_id' => Category::first()->id + 1,
                      'created_at'  => $response['created_at'],
                      'updated_at'  => $response['updated_at'],
                      'id'          => $response['id']
                ]);

        $this->assertDatabaseHas('products', [
                'name'        => $this->product->name,
                'price'       => 11,
                'category_id' => Category::first()->id + 1,
            ]);
    }

    private function getFieldsToUpdateProduct()
    {
        return [
                    'name'        => $this->product->name,
                    'price'       => 11,
                    'category_id' => Category::first()->id + 1
        ]; 
    }
}
