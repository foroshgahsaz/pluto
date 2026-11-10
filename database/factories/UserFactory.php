<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'mobile' => '09'.fake()->unique()->numerify('#########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'mobile_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_super_admin' => false,
            'is_platform_admin' => false,
            'is_active' => true,
            'is_blocked' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (): array => [
            'is_blocked' => true,
        ]);
    }
}
