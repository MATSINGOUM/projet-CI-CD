<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['deposit', 'withdraw', 'transfer']);

        return [
            'account_id' => BankAccount::factory(),
            'type' => $type,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'target_account_id' => $type === 'transfer'
                ? BankAccount::factory()->create()->id
                : null,
        ];
    }

    public function deposit(): self
    {
        return $this->state([
            'type' => 'deposit',
            'target_account_id' => null,
        ]);
    }

    public function withdraw(): self
    {
        return $this->state([
            'type' => 'withdraw',
            'target_account_id' => null,
        ]);
    }

    public function transfer(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'transfer',
                'target_account_id' => BankAccount::factory()->create()->id,
            ];
        });
    }

    public function forAccount(BankAccount $account): self
    {
        return $this->state([
            'account_id' => $account->id,
        ]);
    }

    public function toAccount(BankAccount $account): self
    {
        return $this->state([
            'target_account_id' => $account->id,
        ]);
    }

    public function withAmount(float $amount): self
    {
        return $this->state([
            'amount' => $amount,
        ]);
    }
}
