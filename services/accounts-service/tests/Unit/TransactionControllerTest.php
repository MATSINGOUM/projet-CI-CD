<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $account;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un compte pour les tests
        $this->account = BankAccount::factory()->create([
            'user_id' => 123,
            'balance' => 1000.00
        ]);
    }

    /** @test */
    public function it_can_deposit_money_into_an_account()
    {
        // Arrange
        $data = [
            'account_id' => $this->account->id,
            'amount' => 500
        ];

        // Act
        $response = $this->postJson('/api/deposit', $data);

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Account recharged']);

        // Vérifier le solde mis à jour
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $this->account->id,
            'balance' => 1500
        ]);

        // Vérifier la transaction créée
        $this->assertDatabaseHas('transactions', [
            'account_id' => $this->account->id,
            'type' => 'deposit',
            'amount' => 500
        ]);
    }

    /** @test */
    public function it_validates_deposit_data()
    {
        // Test sans données
        $response = $this->postJson('/api/deposit', []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['account_id', 'amount']);

        // Test avec montant négatif
        $response = $this->postJson('/api/deposit', [
            'account_id' => $this->account->id,
            'amount' => -100
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['amount']);

        // Test avec montant zero
        $response = $this->postJson('/api/deposit', [
            'account_id' => $this->account->id,
            'amount' => 0
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['amount']);

        // Test avec compte inexistant
        $response = $this->postJson('/api/deposit', [
            'account_id' => 999,
            'amount' => 100
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['account_id']);
    }

    /** @test */
    public function it_can_withdraw_money_from_an_account()
    {
        // Arrange
        $data = [
            'account_id' => $this->account->id,
            'amount' => 300
        ];

        // Act
        $response = $this->postJson('/api/withdraw', $data);

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Withdrawal successful']);

        // Vérifier le solde mis à jour
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $this->account->id,
            'balance' => 700
        ]);

        // Vérifier la transaction créée
        $this->assertDatabaseHas('transactions', [
            'account_id' => $this->account->id,
            'type' => 'withdraw',
            'amount' => 300
        ]);
    }

    /** @test */
    public function it_prevents_withdrawal_when_insufficient_balance()
    {
        // Arrange: Essayer de retirer plus que le solde
        $data = [
            'account_id' => $this->account->id,
            'amount' => 1500
        ];

        // Act
        $response = $this->postJson('/api/withdraw', $data);

        // Assert
        $response->assertStatus(400)
                 ->assertJson(['message' => 'Insufficient balance']);

        // Vérifier que le solde n'a pas changé
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $this->account->id,
            'balance' => 1000
        ]);

        // Vérifier qu'aucune transaction n'a été créée
        $this->assertDatabaseCount('transactions', 0);
    }

    /** @test */
    public function it_can_transfer_money_between_accounts()
    {
        // Arrange: Créer un deuxième compte
        $toAccount = BankAccount::factory()->create([
            'user_id' => 456,
            'balance' => 500.00
        ]);

        $data = [
            'from_account_id' => $this->account->id,
            'to_account_id' => $toAccount->id,
            'amount' => 300
        ];

        // Act
        $response = $this->postJson('/api/transfer', $data);

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Transfer successful']);

        // Vérifier les soldes mis à jour
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $this->account->id,
            'balance' => 700
        ]);

        $this->assertDatabaseHas('bank_accounts', [
            'id' => $toAccount->id,
            'balance' => 800
        ]);

        // Vérifier la transaction créée
        $this->assertDatabaseHas('transactions', [
            'account_id' => $this->account->id,
            'type' => 'transfer',
            'amount' => 300,
            'target_account_id' => $toAccount->id
        ]);

        // Vérifier qu'une seule transaction est créée
        $this->assertDatabaseCount('transactions', 1);
    }

    /** @test */
    public function it_validates_transfer_data()
    {
        // Test sans données
        $response = $this->postJson('/api/transfer', []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['from_account_id', 'to_account_id', 'amount']);

        // Test avec montant invalide
        $response = $this->postJson('/api/transfer', [
            'from_account_id' => $this->account->id,
            'to_account_id' => 2,
            'amount' => 0
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['amount']);

        // Test avec même compte
        $response = $this->postJson('/api/transfer', [
            'from_account_id' => $this->account->id,
            'to_account_id' => $this->account->id,
            'amount' => 100
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['to_account_id']);
    }

    /** @test */


    /** @test */
    public function it_can_get_transaction_history_for_an_account()
    {
        // Arrange: Créer quelques transactions
        Transaction::factory()->count(3)->create([
            'account_id' => $this->account->id,
            'type' => 'deposit'
        ]);

        // Créer des transactions pour un autre compte
        $otherAccount = BankAccount::factory()->create(['user_id' => 456]);
        Transaction::factory()->count(2)->create([
            'account_id' => $otherAccount->id
        ]);

        // Act: Récupérer l'historique du compte
        $response = $this->getJson("/api/accounts/{$this->account->id}/transactions");

        // Assert: Ne retourne que les transactions du compte spécifié
        $response->assertStatus(200)
                 ->assertJsonCount(3)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'account_id',
                         'type',
                         'amount',
                         'target_account_id',
                         'created_at',
                         'updated_at'
                     ]
                 ]);
    }

    /** @test */
    public function it_returns_empty_history_for_account_with_no_transactions()
    {
        // Act: Compte sans transactions
        $response = $this->getJson("/api/accounts/{$this->account->id}/transactions");

        // Assert
        $response->assertStatus(200)
                 ->assertJson([]);
    }

    /** @test */
    public function transaction_history_is_ordered_by_creation_date_descending()
    {
        // Arrange: Créer des transactions avec des dates différentes
        $olderTransaction = Transaction::factory()->create([
            'account_id' => $this->account->id,
            'created_at' => now()->subDays(2)
        ]);

        $newerTransaction = Transaction::factory()->create([
            'account_id' => $this->account->id,
            'created_at' => now()
        ]);

        // Act
        $response = $this->getJson("/api/accounts/{$this->account->id}/transactions");
        $transactions = json_decode($response->getContent());

        // Assert: Vérifier l'ordre (le plus récent en premier)
        $this->assertEquals($newerTransaction->id, $transactions[0]->id);
        $this->assertEquals($olderTransaction->id, $transactions[1]->id);
    }

    /** @test */
    public function it_handles_deposit_with_decimal_amounts()
    {
        // Arrange
        $data = [
            'account_id' => $this->account->id,
            'amount' => 123.45
        ];

        // Act
        $response = $this->postJson('/api/deposit', $data);

        // Assert
        $response->assertStatus(200);

        // Vérifier le solde avec précision décimale
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $this->account->id,
            'balance' => 1123.45
        ]);
    }

    /** @test */
    public function it_prevents_transfer_to_nonexistent_account()
    {
        // Arrange
        $data = [
            'from_account_id' => $this->account->id,
            'to_account_id' => 999, // Compte inexistant
            'amount' => 100
        ];

        // Act
        $response = $this->postJson('/api/transfer', $data);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['to_account_id']);
    }

    /** @test */
    public function it_allows_withdrawal_with_exact_balance()
    {
        // Arrange: Retirer exactement le solde
        $data = [
            'account_id' => $this->account->id,
            'amount' => 1000
        ];

        // Act
        $response = $this->postJson('/api/withdraw', $data);

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Withdrawal successful']);

        // Vérifier que le solde est à 0
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $this->account->id,
            'balance' => 0
        ]);
    }

    /** @test */
    public function it_handles_multiple_deposits_correctly()
    {
        // Arrange: Faire plusieurs dépôts
        $data1 = ['account_id' => $this->account->id, 'amount' => 100];
        $data2 = ['account_id' => $this->account->id, 'amount' => 200];
        $data3 = ['account_id' => $this->account->id, 'amount' => 300];

        // Act
        $this->postJson('/api/deposit', $data1);
        $this->postJson('/api/deposit', $data2);
        $this->postJson('/api/deposit', $data3);

        // Assert: Le solde final doit être 1000 + 100 + 200 + 300 = 1600
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $this->account->id,
            'balance' => 1600
        ]);

        // Vérifier qu'il y a 3 transactions
        $this->assertDatabaseCount('transactions', 3);
    }
}
