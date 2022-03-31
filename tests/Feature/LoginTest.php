<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LoginTest extends TestCase
{

    use DatabaseTransactions;

    public function test_loginSuccessfully()
    {
        $loginData = [
            'email' => 'test@test.de',
            'password' => '12345678',
        ];
        $response = $this->post('api/auth/login', $loginData, ['Accept' => 'application/json']);
        $response->assertOk();
    }

    public function test_wrongCredentials()
    {
        $loginData = [
            'email' => 'test@test.de',
            'password' => '123456789',
        ];
        $response = $this->post('api/auth/login', $loginData, ['Accept' => 'application/json']);
        $response->assertUnauthorized();
        $response->assertExactJson([
            'data' => null,
            'message' => 'Credentials not match',
            'status' => 'Error'
        ]);
    }

    /**
     * @dataProvider ProviderLoginNotSuccessfully
     */

    public function test_loginNotSuccessfully($loginData, $exactJson)
    {
        $response = $this->post('api/auth/login', $loginData, ['Accept' => 'application/json']);
        $response->assertUnprocessable();
        $response->assertExactJson($exactJson);
    }

    public function ProviderLoginNotSuccessfully()
    {
        return
            [
                'without email' =>
                    [
                        'loginData' => [
                            'email' => '',
                            'password' => '12345678',
                        ],

                        'exactJson' => [
                            'message' => 'The email field is required.',
                            'errors' => [
                                'email' => [
                                    'The email field is required.',
                                ],
                            ],
                        ],
                    ],
                'without password' =>
                    [
                        'loginData' => [
                            'email' => 'test@test.de',
                            'password' => '',
                        ],

                        'exactJson' => [
                            'message' => 'The password field is required.',
                            'errors' => [
                                'password' => [
                                    'The password field is required.',
                                ],
                            ],
                        ],
                    ],
            ];
    }
}
