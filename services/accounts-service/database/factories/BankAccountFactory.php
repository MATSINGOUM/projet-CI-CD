<?php

namespace Database\Factories;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 1000),
            'account_number' => 'ACC-' . strtoupper($this->faker->bothify('??????????')),
            'type' => $this->faker->randomElement(['courant', 'epargne']),
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'is_active' => $this->faker->boolean(90),
        ];
    }

    public function courant(): self
    {
        return $this->state([
            'type' => 'courant',
        ]);
    }

    public function epargne(): self
    {
        return $this->state([
            'type' => 'epargne',
        ]);
    }

    public function active(): self
    {
        return $this->state([
            'is_active' => true,
        ]);
    }

    public function inactive(): self
    {
        return $this->state([
            'is_active' => false,
        ]);
    }

    public function forUser(int $userId): self
    {
        return $this->state([
            'user_id' => $userId,
        ]);
    }

    public function withBalance(float $balance): self
    {
        return $this->state([
            'balance' => $balance,
        ]);
    }
}
