<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
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

                'email not unique' =>
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

                'password missing' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->unique()->safeEmail(),
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

                'password confirmation missing' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->unique()->safeEmail(),
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

                'password and confirmation empty' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->unique()->safeEmail(),
                            'password' => '',
                            'password_confirmation' => '',
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

                'password confirmation empty' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->unique()->safeEmail(),
                            'password' => '123456',
                            'password_confirmation' => '',
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

                'password confirmation does not match' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->unique()->safeEmail(),
                            'password' => '123456',
                            'password_confirmation' => '23456',
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
                            'email' => $faker->unique()->safeEmail(),
                            'password' => '12345',
                            'password_confirmation' => '12345',
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

                'name missing' =>
                    [
                        'registerData' => [
                            'email' => $faker->unique()->safeEmail(),
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

                'name empty' =>
                    [
                        'registerData' => [
                            'name' => '',
                            'email' => $faker->unique()->safeEmail(),
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

                'name not a string' =>
                    [
                        'registerData' => [
                            'name' => 123,
                            'email' => $faker->unique()->safeEmail(),
                            'password' => '123456',
                            'password_confirmation' => '123456',
                        ],

                        'exactJson' => [
                            'message' => 'The name must be a string.',
                            'errors' => [
                                'name' => [
                                    'The name must be a string.',
                                ],
                            ],
                        ],

                    ],

                'name too long' =>
                    [
                        'registerData' => [
                            'name' => $faker->realTextBetween(256, 500),
                            'email' => $faker->unique()->safeEmail(),
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

            ];
    }

    /**
     * @param $credentials
     * @param $expectedStatus
     * @param $expectedMessage
     *
     * @dataProvider ProviderLogin
     */
    public function test_login($credentials, $expectedStatus, $expectedJson) {
        $response = $this->post('api/auth/login', $credentials, ['Accept' => 'application/json']);
        $response->assertStatus($expectedStatus);

        if ($expectedStatus === 200) {
            $this->assertNotEmpty($response->original['data']['token']);
        } else {
            $response->assertExactJson($expectedJson);
        }
    }

    public function ProviderLogin()
    {
        return
            [
                'login successfully' =>
                    [
                        'credentials' => [
                            'email' => 'test@test.de',
                            'password' => '12345678'
                        ],
                        'expectedStatus' => 200,
                        'expectedJson' => [],
                    ],

                'credentials missing' =>
                    [
                        'credentials' => [

                        ],
                        'expectedStatus' => 422,
                        'expectedJson' => [
                            'message' => 'The email field is required. (and 1 more error)',
                            'errors' => [
                                'email' => [
                                    'The email field is required.',
                                ],
                                'password' => [
                                    'The password field is required.',
                                ],
                            ],
                        ],
                    ],

                'email missing' =>
                    [
                        'credentials' => [
                            'email' => '',
                            'password' => '12345678'
                        ],
                        'expectedStatus' => 422,
                        'expectedJson' => [
                            'message' => 'The email field is required.',
                            'errors' => [
                                'email' => [
                                    'The email field is required.',
                                ],
                            ],
                        ],
                    ],

                'email invalid' =>
                    [
                        'credentials' => [
                            'email' => 'test@',
                            'password' => '12345678'
                        ],
                        'expectedStatus' => 422,
                        'expectedJson' => [
                            'message' => 'The email must be a valid email address.',
                            'errors' => [
                                'email' => [
                                    'The email must be a valid email address.',
                                ],
                            ],
                        ],
                    ],

                'password missing' =>
                    [
                        'credentials' => [
                            'email' => 'test@test.de',
                            'password' => ''
                        ],
                        'expectedStatus' => 422,
                        'expectedJson' => [
                            'message' => 'The password field is required.',
                            'errors' => [
                                'password' => [
                                    'The password field is required.',
                                ],
                            ],
                        ],
                    ],

                'invalid credentials' =>
                    [
                        'credentials' => [
                            'email' => 'test@test.de',
                            'password' => '123456789'
                        ],
                        'expectedStatus' => 401,
                        'expectedJson' => [
                            "data" => null,
                            "message" => "Credentials not match",
                            "status" => "Error",
                        ],
                    ],
            ];
    }
}
