<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Faker\Factory as Faker;

class AuthTest extends TestCase {
    use DatabaseTransactions;

    public string $email = 'test@test.de';

    public function test_registerSuccessfully() {
        $faker        = Faker::create();
        $registerData = [
            'name'                  => $faker->name(),
            'email'                 => $faker->email(),
            'password'              => '12345678',
            'password_confirmation' => '12345678',
        ];
        $response     = $this->post( 'api/auth/register', $registerData, [ 'Accept' => 'application/json' ]
        );
        $response->assertStatus( 200 );
        $this->assertDatabaseHas( 'users',
            [
                'name'  => $registerData['name'],
                'email' => $registerData['email'],
            ]
        );
        $this->assertDatabaseMissing( 'users',
            [
                'name'     => $registerData['name'],
                'email'    => $registerData['email'],
                'password' => '12345678',
            ]
        );
    }

    /**
     * @dataProvider ProviderRegisterNotSuccessfully
     */

    public function test_registerWithoutEmailNotSuccessfully( $registerData, $exactJson ) {
        $response = $this->post( 'api/auth/register', $registerData, [ 'Accept' => 'application/json' ]
        );
        $response->assertStatus( 422 );
        $response->assertExactJson( $exactJson );
    }

    public function ProviderRegisterNotSuccessfully() {
        $faker = Faker::create();

        return
            [
                'without email string'           =>
                    [
                        'registerData' => [
                            'name'                  => $faker->name(),
                            'email'                 => '',
                            'password'              => '12345678',
                            'password_confirmation' => '12345678',
                        ],

                        'exactJson' => [
                            'message' => 'The email field is required.',
                            'errors'  => [
                                'email' => [
                                    'The email field is required.',
                                ],
                            ],
                        ],
                    ],
                'not valid email'                =>
                    [
                        'registerData' => [
                            'name'                  => $faker->name(),
                            'email'                 => 'email@',
                            'password'              => '12345678',
                            'password_confirmation' => '12345678',
                        ],

                        'exactJson' => [
                            'message' => 'The email must be a valid email address.',
                            'errors'  => [
                                'email' => [
                                    'The email must be a valid email address.',
                                ],
                            ],
                        ],

                    ],
                'email not in Response'          =>
                    [
                        'registerData' => [
                            'name'                  => $faker->name(),
                            'password'              => '12345678',
                            'password_confirmation' => '12345678',
                        ],

                        'exactJson' => [
                            'message' => 'The email field is required.',
                            'errors'  => [
                                'email' => [
                                    'The email field is required.',
                                ],
                            ],
                        ],

                    ],
                'password too short'             =>
                    [
                        'registerData' => [
                            'name'                  => $faker->name(),
                            'email'                 => 'asd@asd.de',
                            'password'              => '12345',
                            'password_confirmation' => '12345',
                        ],

                        'exactJson' => [
                            'message' => 'The password must be at least 6 characters.',
                            'errors'  => [
                                'password' => [
                                    'The password must be at least 6 characters.',
                                ],
                            ],
                        ],
                    ],
                'no valid password confirmation' =>
                    [
                        'registerData' => [
                            'name'                  => $faker->name(),
                            'email'                 => $faker->unique()->safeEmail(),
                            'password'              => '12345678',
                            'password_confirmation' => '1234567',
                        ],

                        'exactJson' => [
                            'message' => 'The password confirmation does not match.',
                            'errors'  => [
                                'password' => [
                                    'The password confirmation does not match.',
                                ],
                            ],
                        ],

                    ],
                'no name given'                  =>
                    [
                        'registerData' => [
                            'email'                 => $faker->unique()->safeEmail(),
                            'password'              => '12345678',
                            'password_confirmation' => '12345678',
                        ],

                        'exactJson' => [
                            'message' => 'The name field is required.',
                            'errors'  => [
                                'name' => [
                                    'The name field is required.',
                                ],
                            ],
                        ],

                    ],
                'name too long'                  =>
                    [
                        'registerData' => [
                            'name'                  => 'sdfghjsdhfgjshdfkjghsdjkfghskjdfhgjksdhfgjkshdfkjghsdjkfghsjkdfhgjksdhfgkjshdfkjghsdjkfghskjdfghjksdhfgjksdhfgjkhsdfjkghsdjkfghskjdfghksjdfhgkjsdhfgkjsdhfgkjhsdkjfgskdjfhgsjkdfhgjkadfhgjkadhfgkjahdfjkghadkjfghajkdfghjkadfhgkjadhfgkjahfgjkahfkjghakjdfghakjdfhgkajdfhgkjahdfgkjahfdgjkahdfgkjahdfjkghsdkfgjhskjfghskjfghskjdfghkjsfhgjkshfgkjshfgkjshdfjkghakjdfghskjfghkjsfdhgjkshfdgkjsdhfgkjshdfgkjshfdgkjhsdkjfghskjdfghskjfdhgjkshdfgkjshfgjkshfdkjghsjkfghskjdfhgkjshfgkjshfdgjkhsdfjkghsjkdfghskjdfghjksfghkjsdfhgkjsdfhgjskdfhgkjsdfhgkjsdfhgkjsdfhgjkshdfgkjshdfgkjshdfgkjshdfgkjsdhfg',
                            'email'                 => $faker->unique()->safeEmail(),
                            'password'              => '12345678',
                            'password_confirmation' => '12345678',
                        ],

                        'exactJson' => [
                            'message' => 'The name must not be greater than 255 characters.',
                            'errors'  => [
                                'name' => [
                                    'The name must not be greater than 255 characters.',
                                ],
                            ],
                        ],

                    ],
                'no data given'                  =>
                    [
                        'registerData' => [
                            'name'                  => '',
                            'email'                 => '',
                            'password'              => '',
                            'password_confirmation' => '',
                        ],

                        'exactJson' => [
                            'message' => 'The name field is required. (and 2 more errors)',
                            'errors'  => [
                                'name'     => [
                                    'The name field is required.',
                                ],
                                'email'    => [
                                    'The email field is required.',
                                ],
                                'password' => [
                                    'The password field is required.',
                                ],
                            ],
                        ],

                    ],
                'email is taken'                 =>
                    [
                        'registerData' =>
                            [
                                'name'                  => $faker->name(),
                                'email'                 => $this->email,
                                'password'              => '123456789',
                                'password_confirmation' => '123456789',
                            ],


                        'exactJson' => [
                            'message' => 'The email has already been taken.',
                            'errors'  => [
                                'email' => [
                                    'The email has already been taken.',
                                ],
                            ],
                        ],

                    ],
            ];
    }
}
