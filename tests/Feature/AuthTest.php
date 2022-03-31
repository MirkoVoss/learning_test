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
     * @dataProvider ProviderLoginTests
     */

    public function tests_login($loginData, $exactJson = null, $status)
    {
        $response = $this->post('api/auth/login', $loginData, ['Accept' => 'application/json']
        );
        $response->assertStatus($status);
        if ($exactJson) $response->assertExactJson($exactJson);
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
                'email duplicatet' =>
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
                'name not in Rasponse' =>
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
                'name to long' =>
                    [
                        'registerData' => [
                            'name' => 'asöknfidsbvliasdbfökngbkdshirehoghdofhohfoerihjfoirehjgoiehrjoighoisdhoihfoihopighoidhowshfgpoiuhgpierhteüiuurhtüophtpiehrgpiurehphsdbfpibdföjnadäofhp8dfhbiöwenfefladngkwrndgorejnfogjnrwojgoerjgüoprjg+pjrweäojgorewüihjgüojrew+ogjoüirehjtliäjwddpiohüeorijhtüoirjüotrhj+or',
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
                'password not duplicated' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->email(),
                            'password' => '12345678',
                            'password_confirmation' => '123456789',
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
                'without password confirmation' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->email(),
                            'password' => '12345678',
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
                'without password informations' =>
                    [
                        'registerData' => [
                            'name' => $faker->name(),
                            'email' => $faker->email(),
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
            ];
    }

    public function ProviderLoginTests()
    {
        $faker = Faker::create();

        return
            [
                'success' =>
                    [
                        'loginData' => [
                            'email' => 'test@test.de',
                            'password' => '12345678',
                        ],

                        'exactJson' => null,

                        'status' => 200
                    ],

                'without password' =>
                    [
                        'loginData' => [
                            'email' => 'test@test.de',
                            'password' => '',
                        ],

                        'exactJson' => [
                            "message" => "The password field is required.",
                            "errors" => [
                                "password" => [
                                    "The password field is required."
                                ]
                            ]
                        ],

                        'status' => 422,
                    ],

                'without mail' =>
                    [
                        'loginData' => [
                            'email' => '',
                            'password' => '12345678',
                        ],

                        'exactJson' => [
                            "message" => "The email field is required.",
                            "errors" => [
                                "email" => [
                                    "The email field is required."
                                ]
                            ]
                        ],

                        'status' => 422,
                    ],

                'incorrect mail' =>
                    [
                        'loginData' => [
                            'email' => 'test@test.com',
                            'password' => '12345678',
                        ],

                        'exactJson' => [
                            'data' => null,
                            'message' => "Credentials not match",
                            'status' => "Error"
                        ],

                        'status' => 401,
                    ],

                'incorrect password' =>
                    [
                        'loginData' => [
                            'email' => 'test@test.de',
                            'password' => '123456789',
                        ],

                        'exactJson' => [
                            'data' => null,
                            'message' => "Credentials not match",
                            'status' => "Error"
                        ],

                        'status' => 401,
                    ],

            ];
    }
}
