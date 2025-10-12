<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class AuthenticationIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les rôles de base
        Role::factory()->create([
            'id' => 1,
            'name' => 'User',
            'slug' => 'user',
            'is_active' => true
        ]);

        Role::factory()->create([
            'id' => 2,
            'name' => 'Admin',
            'slug' => 'admin',
            'is_active' => true
        ]);
    }

    public function test_complete_user_registration_and_login_flow()
    {
        Notification::fake();

        // 1. Inscription
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+33123456789'
        ];

        $registerResponse = $this->postJson('/api/auth/register', $userData);
        $registerResponse->assertStatus(201);

        $registerData = $registerResponse->json();
        $this->assertArrayHasKey('token', $registerData['data']);
        $firstToken = $registerData['data']['token'];

        // Vérifier que l'utilisateur a été créé
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        // Marquer l'email comme vérifié pour permettre la connexion
        $user = User::where('email', 'test@example.com')->first();
        $user->markEmailAsVerified();

        // 2. Test de connexion avec email
        $loginResponse = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'password123'
        ]);
        $loginResponse->assertStatus(200);

        $loginData = $loginResponse->json();
        $secondToken = $loginData['data']['token'];
        $this->assertNotEquals($firstToken, $secondToken);

        // 3. Test de connexion avec téléphone
        $phoneLoginResponse = $this->postJson('/api/auth/login', [
            'login' => '+33123456789',
            'password' => 'password123'
        ]);
        $phoneLoginResponse->assertStatus(200);

        // 4. Test d'accès au profil
        $meResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $secondToken
        ])->getJson('/api/auth/me');

        $meResponse->assertStatus(200);
        $meData = $meResponse->json();
        $this->assertEquals('test@example.com', $meData['user']['email']);

        // 5. Test de déconnexion
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $secondToken
        ])->postJson('/api/auth/logout');

        $logoutResponse->assertStatus(200);
    }

    public function test_email_verification_flow()
    {
        Notification::fake();

        // 1. Créer un utilisateur non vérifié
        $user = User::factory()->create([
            'email_verified_at' => null,
            'role_id' => 1
        ]);

        // 2. Demander un email de vérification
        $this->actingAs($user, 'sanctum');

        $sendResponse = $this->postJson('/api/auth/email/verification-notification');
        $sendResponse->assertStatus(200);

        // Vérifier que la notification a été envoyée
        Notification::assertSentTo($user, VerifyEmail::class);

        // 3. Vérifier le statut de vérification
        $statusResponse = $this->getJson('/api/auth/email/verification-status');
        $statusResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'verified' => false
                ]
            ]);

        // 4. Marquer l'email comme vérifié
        $user->markEmailAsVerified();

        // 5. Vérifier le nouveau statut
        $newStatusResponse = $this->getJson('/api/auth/email/verification-status');
        $newStatusResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'verified' => true
                ]
            ]);
    }

    public function test_authentication_with_different_user_roles()
    {
        // Test avec un utilisateur admin
        $adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'role_id' => 2,
            'email_verified_at' => now()
        ]);

        $adminLoginResponse = $this->postJson('/api/auth/login', [
            'login' => 'admin@example.com',
            'password' => 'admin123'
        ]);

        $adminLoginResponse->assertStatus(200);
        $adminData = $adminLoginResponse->json();
        $this->assertEquals('admin', $adminData['data']['user']['role']['slug']);

        // Test avec un utilisateur normal
        $normalUser = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('user123'),
            'role_id' => 1,
            'email_verified_at' => now()
        ]);

        $userLoginResponse = $this->postJson('/api/auth/login', [
            'login' => 'user@example.com',
            'password' => 'user123'
        ]);

        $userLoginResponse->assertStatus(200);
        $userData = $userLoginResponse->json();
        $this->assertEquals('user', $userData['data']['user']['role']['slug']);
    }

    public function test_api_response_consistency()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role_id' => 1
        ]);

        // Toutes les réponses d'authentification doivent avoir une structure cohérente
        $responses = [
            $this->postJson('/api/auth/login', [
                'login' => $user->email,
                'password' => 'password'
            ]),
        ];

        foreach ($responses as $response) {
            if ($response->status() === 200) {
                $data = $response->json();

                // Vérifier la structure de base
                $this->assertArrayHasKey('success', $data);
                $this->assertArrayHasKey('message', $data);

                if (isset($data['data']['user'])) {
                    // Vérifier la structure UserResource
                    $user = $data['data']['user'];
                    $this->assertArrayHasKey('id', $user);
                    $this->assertArrayHasKey('name', $user);
                    $this->assertArrayHasKey('email', $user);

                    // Vérifier que les données sensibles ne sont pas exposées
                    $this->assertArrayNotHasKey('password', $user);
                    $this->assertArrayNotHasKey('remember_token', $user);
                }
            }
        }
    }

    public function test_security_measures()
    {
        // Test que les mots de passe faibles sont rejetés
        $weakPasswordResponse = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123'
        ]);
        $weakPasswordResponse->assertStatus(422);

        // Test que les emails dupliqués sont rejetés
        User::factory()->create(['email' => 'existing@example.com']);

        $duplicateEmailResponse = $this->postJson('/api/auth/register', [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        $duplicateEmailResponse->assertStatus(422);

        // Test que les utilisateurs non vérifiés ne peuvent pas se connecter
        $unverifiedUser = User::factory()->create([
            'email' => 'unverified@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null
        ]);

        $unverifiedLoginResponse = $this->postJson('/api/auth/login', [
            'login' => 'unverified@example.com',
            'password' => 'password123'
        ]);
        $unverifiedLoginResponse->assertStatus(403);
    }
}
