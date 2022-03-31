<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;
use Faker\Factory as Faker;

class AuthTest extends TestCase
{

    use DatabaseTransactions;

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
                'email not in Request' =>
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
                'without password' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->email(),
                            'password' => null,
                            'password_confirmation' => '12345678',
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
                'without password confirmation' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->email(),
                            'password' => '123456',
                        ],

                        'exactJson' => [
                            'message' => 'The password confirmation does not match.',
                            'errors' => [
                                'password' => [
                                    'The password confirmation does not match.',
                                ],
                            ],
                        ],
                    ],
                'password too short' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->email(),
                            'password' => '1234',
                            'password_confirmation' => '1234',
                        ],

                        'exactJson' => [
                            'message' => 'The password must be at least 6 characters.',
                            'errors' => [
                                'password' => [
                                    'The password must be at least 6 characters.',
                                ],
                            ],
                        ],
                    ],
                'name is null' =>
                    [
                        'registerData' => [
                            'name' => null,
                            'email' => $faker->email(),
                            'password' => '123456',
                            'password_confirmation' => '123456',
                        ],

                        'exactJson' => [
                            'message' => 'The name field is required.',
                            'errors' => [
                                'name' => [
                                    'The name field is required.',
                                ],
                            ],
                        ],
                    ],
                'name too long' =>
                    [
                        'registerData' => [
                            'name' => Str::random(300),
                            'email' => $faker->email(),
                            'password' => '123456',
                            'password_confirmation' => '123456',
                        ],

                        'exactJson' => [
                            'message' => 'The name must not be greater than 255 characters.',
                            'errors' => [
                                'name' => [
                                    'The name must not be greater than 255 characters.',
                                ],
                            ],
                        ],
                    ],
                'email is taken' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => 'test@test.de',
                            'password' => '123456',
                            'password_confirmation' => '123456',
                        ],

                        'exactJson' => [
                            'message' => 'The email has already been taken.',
                            'errors' => [
                                'email' => [
                                    'The email has already been taken.',
                                ],
                            ],
                        ],
                    ],
            ];
    }
}
