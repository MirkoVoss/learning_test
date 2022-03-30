<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory as Faker;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registerSuccessfully()
    {
        $faker = Faker::create();
        $registerData = [
            'name' => $faker->name(),
            'email' => $faker->unique()->safeEmail(),
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];
        $response = $this->post('api/auth/register', $registerData, ['Accept' => 'application/json']
        );
        $response->assertStatus(200);
        $this->assertDatabaseHas('users',
            [
                'name' => $registerData['name'],
                'email' => $registerData['email'],
            ]
        );
        $this->assertDatabaseMissing('users',
            [
                'name' => $registerData['name'],
                'email' => $registerData['email'],
                'password' => '12345678',
            ]
        );
    }

    /**
     * @dataProvider ProviderRegisterNotSuccessfully
     */

    public function test_registerWithoutEmailNotSuccessfully($registerData, $exactJson)
    {
        $response = $this->post('api/auth/register', $registerData, ['Accept' => 'application/json']
        );
        $response->assertStatus(422);
        $response->assertExactJson($exactJson);
    }

    public function ProviderRegisterNotSuccessfully()
    {
        $faker = Faker::create();

        return
            [
                'without email string' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => '',
                            'password' => '12345678',
                            'password_confirmation' => '12345678',
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
                'not valid email' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => 'email@',
                            'password' => '12345678',
                            'password_confirmation' => '12345678',
                        ],

                        'exactJson' => [
                            'message' => 'The email must be a valid email address.',
                            'errors' => [
                                'email' => [
                                    'The email must be a valid email address.',
                                ],
                            ],
                        ],

                    ],
                'email not in Response' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'password' => '12345678',
                            'password_confirmation' => '12345678',
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
            ];
    }
}
