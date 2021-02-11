<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Models\Product;
class LoginControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    
    public function test_login_with_invalid_credentials()
    {
        $this->post(route('api.login'), [])->assertStatus(HttpResponse::HTTP_UNAUTHORIZED)
                                     ->assertJson(['error' => 'invalid credentials']);

        $this->post(route('api.login'), [
            'email' => 'teste@email.com'
        ])->assertStatus(HttpResponse::HTTP_UNAUTHORIZED)
          ->assertJson(['error' => 'invalid credentials']);

        $this->post(route('api.login'), [
            'password' => 'teste@email.com'
        ])->assertStatus(HttpResponse::HTTP_UNAUTHORIZED)
          ->assertJson(['error' => 'invalid credentials']);

        $this->post(route('api.login'), [
            'email'    => 'teste@email.com',
            'password' => 'teste@email.com'
        ])->assertStatus(HttpResponse::HTTP_UNAUTHORIZED)
          ->assertJson(['error' => 'invalid credentials']);
    }

    public function test_should_not_allow_access_route()
    {
        $productId = Product::first()->id;
        $this->get(route('api.products.index'))->assertStatus(HttpResponse::HTTP_UNAUTHORIZED);
        $this->post(route('api.products.store'),[])->assertStatus(HttpResponse::HTTP_UNAUTHORIZED);
        $this->delete(route('api.products.destroy',$productId))->assertStatus(HttpResponse::HTTP_UNAUTHORIZED);
        $this->put(route('api.products.update', $productId))->assertStatus(HttpResponse::HTTP_UNAUTHORIZED);
    }

    public function test_should_be_able_to_auth_an_user()
    {
        $user     = User::first();
        $response = $this->post(route('api.login'), [
            'email'    => $user->email, 
            'password' => 'password'
        ])->assertStatus(HttpResponse::HTTP_OK);

        $response->assertExactJson([
                                        'user' => [ 
                                            "id"                => $user->id,
                                            "name"              => $user->name,
                                            "email"             => $user->email,
                                            "email_verified_at" => $user->email_verified_at,
                                            "created_at"        => $user->created_at,
                                            "updated_at"        => $user->updated_at,
                                        ],
                                        'token' => $response['token']
                                  ]);
    }
}
