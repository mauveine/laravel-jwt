<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_server_works () {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_register () {
        $name = 'Test Name';
        $userEmail = 'test@test.example';
        $password = 'secret';
        $request = $this->json('POST', '/api/auth/register', [
            'name' => $name,
            'email' => $userEmail,
            'password' => $password,
            'password_confirmation' => $password
        ]);

        $request->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'name', 'email'
                ]
            ])->assertJson([
                'user' => [
                    'name' => $name,
                    'email' => $userEmail
                ]
            ]);
    }

    public function test_login () {
        $user = User::factory(1)->createOne();
        $request = $this->json('POST', '/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        $request->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'expires_in',
                'user' => [
                    'name', 'email'
                ]
            ])->assertJson([
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
    }

    public function test_refresh () {
        $user = User::factory(1)->createOne();
        $request = $this->actingAsJWT($user)
            ->json('POST', '/api/auth/refresh');
        $request->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'expires_in',
                'user' => [
                    'name', 'email'
                ]
            ]);
    }

    public function test_logout () {
        $user = User::factory(1)->createOne();
        $request = $this->actingAsJWT($user)
            ->json('POST', '/api/auth/logout');
        $request->assertStatus(200)
            ->assertExactJson([
                'message' => 'User successfully signed out'
            ]);
    }

    public function test_get_current_user () {
        $user = User::factory(1)->createOne();
        $request = $this->actingAsJWT($user)
            ->json('GET', '/api/auth/user');
        $request->assertStatus(200)
            ->assertJson([
                'name' => $user->name,
                'email' => $user->email
            ]);
    }
}
