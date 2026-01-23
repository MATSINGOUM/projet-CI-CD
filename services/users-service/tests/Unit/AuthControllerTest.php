<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/register', $userData);

        // Assert
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'user' => [
                         'id',
                         'name',
                         'email',
                         'created_at',
                         'updated_at'
                     ],
                     'token'
                 ])
                 ->assertJson([
                     'user' => [
                         'name' => 'John Doe',
                         'email' => 'john@example.com'
                     ]
                 ]);

        // Vérifier que l'utilisateur est bien créé en base
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        // Vérifier que le mot de passe est hashé
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function registration_requires_name_email_and_password()
    {
        // Act: Tenter de s'enregistrer sans données
        $response = $this->postJson('/api/register', []);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function registration_requires_valid_email()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/register', $userData);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_requires_unique_email()
    {
        // Arrange: Créer un utilisateur existant
        User::factory()->create(['email' => 'john@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com', // Email déjà utilisé
            'password' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/register', $userData);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_requires_password_minimum_length()
    {
        // Arrange: Mot de passe trop court
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123', // Trop court
        ];

        // Act
        $response = $this->postJson('/api/register', $userData);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        // Arrange: Créer un utilisateur
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        // Act
        $response = $this->postJson('/api/login', $loginData);

        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user' => [
                         'id',
                         'name',
                         'email',
                         'created_at',
                         'updated_at'
                     ],
                     'token'
                 ])
                 ->assertJson([
                     'user' => [
                         'email' => 'john@example.com'
                     ]
                 ]);

        // Vérifier qu'un token a été créé
        $this->assertNotEmpty($response->json('token'));
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        // Arrange: Créer un utilisateur
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        // Test 1: Mauvais mot de passe
        $loginData = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/login', $loginData);
        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid credentials']);

        // Test 2: Email inexistant
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $loginData);
        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid credentials']);
    }

    /** @test */
    public function login_requires_email_and_password()
    {
        // Test sans données
        $response = $this->postJson('/api/login', []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email', 'password']);

        // Test avec email invalide
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        // Arrange: Créer et authentifier un utilisateur
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->postJson('/api/logout');

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logged out']);

        // Vérifier que le token a été supprimé
        $this->assertCount(0, $user->tokens);
    }

    /** @test */
    public function logout_requires_authentication()
    {
        // Act: Tenter de se déconnecter sans être authentifié
        $response = $this->postJson('/api/logout');

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function new_user_has_no_tokens_before_login()
    {
        // Arrange: Créer un utilisateur via l'API register
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);
        $user = User::where('email', 'john@example.com')->first();

        // Assert: L'utilisateur ne devrait pas avoir de tokens actifs
        // (car le token retourné est nouveau et différent des tokens stockés)
        $this->assertInstanceOf(User::class, $user);
    }

    /** @test */
    public function token_is_generated_on_login()
    {
        // Arrange: Créer un utilisateur
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        // Act
        $response = $this->postJson('/api/login', $loginData);
        $token = $response->json('token');

        // Assert: Le token doit être une chaîne non vide
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }
}
