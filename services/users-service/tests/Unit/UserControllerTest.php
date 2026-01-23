<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un utilisateur admin pour les tests
        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123')
        ]);
    }

    /** @test */
    public function authenticated_user_can_list_all_users()
    {
        // Arrange: Authentifier l'utilisateur et créer d'autres utilisateurs
        Sanctum::actingAs($this->adminUser);

        User::factory()->count(3)->create();

        // Act
        $response = $this->getJson('/api/users');

        // Assert
        $response->assertStatus(200)
                 ->assertJsonCount(4) // Admin + 3 utilisateurs
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'name',
                         'email',
                         'created_at',
                         'updated_at'
                     ]
                 ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_list_users()
    {
        // Act: Tenter d'accéder sans authentification
        $response = $this->getJson('/api/users');

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_create_new_user()
    {
        // Arrange: Authentifier l'utilisateur
        Sanctum::actingAs($this->adminUser);

        $newUserData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123'
        ];

        // Act
        $response = $this->postJson('/api/users', $newUserData);

        // Assert
        $response->assertJsonStructure([
                     'id',
                     'name',
                     'email',
                     'created_at',
                     'updated_at'
                 ])
                 ->assertJson([
                     'name' => 'New User',
                     'email' => 'newuser@example.com'
                 ]);

        // Vérifier en base de données
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com'
        ]);

        // Vérifier que le mot de passe est hashé
        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function user_creation_requires_validation()
    {
        // Arrange: Authentifier l'utilisateur
        Sanctum::actingAs($this->adminUser);

        // Test 1: Données manquantes
        $response = $this->postJson('/api/users', []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email', 'password']);

        // Test 2: Email invalide
        $response = $this->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);

        // Test 3: Email déjà utilisé
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123'
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);

        // Test 4: Mot de passe trop court
        $response = $this->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123' // Trop court
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function authenticated_user_can_view_specific_user()
    {
        // Arrange: Authentifier l'utilisateur et créer un autre utilisateur
        Sanctum::actingAs($this->adminUser);

        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com'
        ]);

        // Act
        $response = $this->getJson("/api/users/{$user->id}");

        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'name' => 'Jane Doe',
                     'email' => 'jane@example.com'
                 ]);
    }

    /** @test */
    public function returns_404_when_user_not_found()
    {
        // Arrange: Authentifier l'utilisateur
        Sanctum::actingAs($this->adminUser);

        // Act: Tenter de récupérer un utilisateur inexistant
        $response = $this->getJson('/api/users/999');

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function authenticated_user_can_update_user()
    {
        // Arrange: Authentifier l'utilisateur et créer un utilisateur à modifier
        Sanctum::actingAs($this->adminUser);

        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => Hash::make('oldpassword')
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => 'newpassword123'
        ];

        // Act
        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                     'name' => 'Updated Name',
                     'email' => 'updated@example.com'
                 ]);

        // Vérifier les mises à jour en base
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);

        // Vérifier que le mot de passe a été changé
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /** @test */
    public function user_update_can_update_partial_data()
    {
        // Arrange: Authentifier l'utilisateur
        Sanctum::actingAs($this->adminUser);

        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'password' => Hash::make('originalpassword')
        ]);

        // Test 1: Mettre à jour seulement le nom
        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'New Name Only'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'name' => 'New Name Only',
                     'email' => 'original@example.com'
                 ]);

        // Test 2: Mettre à jour seulement l'email
        $user->refresh();
        $response = $this->putJson("/api/users/{$user->id}", [
            'email' => 'newemail@example.com'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'name' => 'New Name Only', // Doit rester le même
                     'email' => 'newemail@example.com'
                 ]);

        // Test 3: Mettre à jour seulement le mot de passe
        $user->refresh();
        $oldPasswordHash = $user->password;

        $response = $this->putJson("/api/users/{$user->id}", [
            'password' => 'newsecurepassword'
        ]);

        $response->assertStatus(200);

        // Vérifier que le mot de passe a changé
        $user->refresh();
        $this->assertNotEquals($oldPasswordHash, $user->password);
        $this->assertTrue(Hash::check('newsecurepassword', $user->password));
    }

    /** @test */


    /** @test */


    /** @test */
    public function authenticated_user_can_delete_user()
    {
        // Arrange: Authentifier l'utilisateur et créer un utilisateur à supprimer
        Sanctum::actingAs($this->adminUser);

        $user = User::factory()->create();

        // Act
        $response = $this->deleteJson("/api/users/{$user->id}");

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['message' => 'User deleted']);

        // Vérifier que l'utilisateur a été supprimé
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }


    /** @test */

    /** @test */
    public function user_can_delete_themselves()
    {
        // Arrange: Authentifier l'utilisateur (qui sera supprimé)
        $userToDelete = User::factory()->create();
        Sanctum::actingAs($userToDelete);

        // Act: L'utilisateur se supprime lui-même
        $response = $this->deleteJson("/api/users/{$userToDelete->id}");

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['message' => 'User deleted']);

        // Vérifier que l'utilisateur a été supprimé
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    /** @test */
    public function password_is_hidden_in_responses()
    {
        // Arrange: Authentifier l'utilisateur
        Sanctum::actingAs($this->adminUser);

        // Créer un utilisateur via l'API
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/users', $userData);
        $userResponse = $response->json();

        // Assert: Le mot de passe ne doit pas être dans la réponse
        $this->assertArrayNotHasKey('password', $userResponse);

        // Récupérer l'utilisateur via l'API show
        $showResponse = $this->getJson("/api/users/{$userResponse['id']}");
        $showData = $showResponse->json();

        $this->assertArrayNotHasKey('password', $showData);
    }

    /** @test */

}
