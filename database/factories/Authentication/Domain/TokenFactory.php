<?php

namespace Database\Factories\Authentication\Domain;

use App\Authentication\Domain\ExpiresIn;
use App\Authentication\Domain\ShopId;
use App\Authentication\Domain\Token;
use App\Authentication\Domain\TokenType;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use function random_int;

class TokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Token::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            'shop_id'       => new ShopId(Uuid::fromString($this->faker->uuid)),
            'access_token'  => Str::random(),
            'refresh_token' => Str::random(),
            'token_type'    => TokenType::BEARER(),
            'expires_in'    => new ExpiresIn(random_int(1, 3600)),
        ];
    }
}
