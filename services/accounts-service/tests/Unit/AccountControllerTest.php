<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\BankAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_accounts_for_a_user()
    {
        // Arrange: Créer des comptes pour un user_id spécifique
        $userId = 123;

        BankAccount::factory()->count(2)->create([
            'user_id' => $userId
        ]);

        // Créer d'autres comptes pour un autre utilisateur
        BankAccount::factory()->count(3)->create([
            'user_id' => 456
        ]);

        // Act: Appeler l'API
        $response = $this->getJson("/api/users/{$userId}/accounts");

        // Assert: Ne retourne que les comptes de l'utilisateur 123
        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'user_id',
                         'account_number',
                         'type',
                         'balance',
                         'is_active'
                     ]
                 ])
                 ->assertJsonFragment(['user_id' => $userId]);
    }

    /** @test */
    public function it_returns_empty_array_when_user_has_no_accounts()
    {
        // Act: User sans comptes
        $response = $this->getJson('/api/users/999/accounts');

        // Assert
        $response->assertStatus(200)
                 ->assertJson([]);
    }

    /** @test */


    /** @test */
    public function it_validates_required_fields_when_creating_account()
    {
        // Act: Appeler l'API avec des données invalides
        $response = $this->postJson('/api/accounts', []);

        // Assert: Vérifier les erreurs de validation
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['user_id', 'type']);
    }

    /** @test */
    public function it_validates_account_type_when_creating_account()
    {
        // Arrange: Données avec type invalide
        $data = [
            'user_id' => 123,
            'type' => 'invalid_type'
        ];

        // Act
        $response = $this->postJson('/api/accounts', $data);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function it_validates_user_id_is_integer()
    {
        // Arrange: Données avec user_id non numérique
        $data = [
            'user_id' => 'not-an-integer',
            'type' => 'courant'
        ];

        // Act
        $response = $this->postJson('/api/accounts', $data);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function it_can_show_a_specific_account()
    {
        // Arrange: Créer un compte
        $account = BankAccount::factory()->create([
            'user_id' => 123
        ]);

        // Act: Récupérer le compte
        $response = $this->getJson("/api/accounts/{$account->id}");

        // Assert: Vérifier la réponse
        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $account->id,
                     'user_id' => $account->user_id,
                     'account_number' => $account->account_number
                 ]);
    }

    /** @test */
    public function it_returns_404_when_account_not_found()
    {
        // Act: Essayer de récupérer un compte inexistant
        $response = $this->getJson('/api/accounts/999');

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_deactivate_an_account()
    {
        // Arrange: Créer un compte actif
        $account = BankAccount::factory()->active()->create([
            'user_id' => 123,
        ]);

        // Act: Désactiver le compte
        $response = $this->patchJson("/api/accounts/{$account->id}/deactivate");

        // Assert: Vérifier la désactivation
        $response->assertStatus(200)
                 ->assertJson([
                     'is_active' => false
                 ]);

        // Vérifier en base de données
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'is_active' => false
        ]);
    }

    /** @test */
    public function it_can_deactivate_already_inactive_account()
    {
        // Arrange: Créer un compte déjà inactif
        $account = BankAccount::factory()->inactive()->create([
            'user_id' => 123,
        ]);

        // Act: Désactiver le compte (à nouveau)
        $response = $this->patchJson("/api/accounts/{$account->id}/deactivate");

        // Assert: Doit rester inactif
        $response->assertStatus(200)
                 ->assertJson([
                     'is_active' => false
                 ]);
    }

    /** @test */
    public function it_returns_404_when_deactivating_nonexistent_account()
    {
        // Act: Essayer de désactiver un compte inexistant
        $response = $this->patchJson('/api/accounts/999/deactivate');

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function account_number_is_generated_automatically_and_unique()
    {
        // Arrange: Créer plusieurs comptes
        $userId = 123;
        $data = [
            'user_id' => $userId,
            'type' => 'epargne'
        ];

        // Act: Créer plusieurs comptes
        $response1 = $this->postJson('/api/accounts', $data);
        $response2 = $this->postJson('/api/accounts', $data);

        $account1 = json_decode($response1->getContent());
        $account2 = json_decode($response2->getContent());

        // Assert: Vérifier le format et l'unicité
        $this->assertStringStartsWith('ACC-', $account1->account_number);
        $this->assertEquals(14, strlen($account1->account_number));

        // Les numéros doivent être différents
        $this->assertNotEquals($account1->account_number, $account2->account_number);
    }

    /** @test */
    public function balance_is_always_zero_on_account_creation()
    {
        // Arrange: Tentative de créer un compte avec balance (sera ignoré)
        $data = [
            'user_id' => 123,
            'type' => 'courant',
        ];

        // Act
        $response = $this->postJson('/api/accounts', $data);
        $account = json_decode($response->getContent());

        // Assert: Balance doit être 0
        $this->assertEquals(0, $account->balance);
    }
}
