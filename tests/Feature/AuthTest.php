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

    /**
     * @dataProvider ProviderLogin
     */

    public function test_login($loginData, $exactJson, $statusCode)
    {
        $response = $this->post('api/auth/login', $loginData, ['Accept' => 'application/json']
        );
        $response->assertStatus($statusCode);
        if($statusCode === 200){
            $response->assertJson($exactJson);
        }
        else{
            $response->assertExactJson($exactJson);
        }

    }

    public function ProviderLogin()
    {

        return
            [
                'login successful' => [
                    'loginData' => [
                        'email' => 'test@test.de',
                        'password' => '12345678',
                    ],

                    'exactJson' => [
                        'message' => null,
                        'status' => 'Success'
                    ],
                    'statusCode' => 200,
                ],
                'wrong email' => [
                    'loginData' => [
                        'email' => 'test2@test2.de',
                        'password' => '12345678',
                    ],

                    'exactJson' => [
                        'data'=> null,
                        'message' => 'Credentials not match',
                        'status' => 'Error'
                    ],
                    'statusCode' => 401,
                ],
                'wrong password' => [

                    'loginData' => [
                        'email' => 'test@test.de',
                        'password' => '123456789',
                    ],

                    'exactJson' => [
                        'data'=> null,
                        'message' => 'Credentials not match',
                        'status' => 'Error'
                    ],
                    'statusCode' => 401,
                ],
                'short password' => [

                    'loginData' => [
                        'email' => 'test@test.de',
                        'password' => '12345',
                    ],

                    'exactJson' => [
                        'message' => 'The password must be at least 6 characters.',
                        'errors' => [
                            'password' => [
                                'The password must be at least 6 characters.',
                            ],
                        ],
                    ],
                    'statusCode' => 422,
                ]
            ];
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
                'without name string' =>
                    [
                        'registerData' => [
                            'name' => '',
                            'email' => $faker->email(),
                            'password' => '12345678',
                            'password_confirmation' => '12345678',
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
                'name not in Response' =>
                    [
                        'registerData' => [
                            'email' => $faker->email(),
                            'password' => '12345678',
                            'password_confirmation' => '12345678',
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
                'not valid name' =>
                    [
                        'registerData' => [
                            'name' => $faker->text(1000),
                            'email' => $faker->email(),
                            'password' => '12345678',
                            'password_confirmation' => '12345678',
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
                'email already registered' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => 'test@test.de',
                            'password' => '12345678',
                            'password_confirmation' => '12345678',
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
                'different passwords' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->email(),
                            'password' => '1234564778',
                            'password_confirmation' => '12345678',
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
                'short password' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->email(),
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
            ];
    }
}
