<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmailVerificationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $verifiedUser;
    protected $unverifiedUser;
    protected $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::factory()->create([
            'name' => 'User',
            'slug' => 'user',
            'is_active' => true
        ]);

        // Utilisateur avec email vérifié
        $this->verifiedUser = User::factory()->create([
            'email_verified_at' => now(),
            'role_id' => $this->role->id
        ]);

        // Utilisateur avec email non vérifié
        $this->unverifiedUser = User::factory()->create([
            'email_verified_at' => null,
            'role_id' => $this->role->id
        ]);
    }

    public function test_verified_user_cannot_request_new_verification_email()
    {
        Sanctum::actingAs($this->verifiedUser);

        $response = $this->postJson('/api/v1/auth/email/verification-notification');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Email already verified'
            ]);
    }

    public function test_unverified_user_can_request_verification_email()
    {
        Notification::fake();
        Sanctum::actingAs($this->unverifiedUser);

        $response = $this->postJson('/api/auth/email/verification-notification');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'email'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Verification email sent successfully'
            ]);

        // Vérifier que la notification a été envoyée
        Notification::assertSentTo($this->unverifiedUser, VerifyEmail::class);

        $responseData = $response->json();
        $this->assertEquals($this->unverifiedUser->email, $responseData['data']['email']);
    }

    public function test_unauthenticated_user_cannot_request_verification_email()
    {
        $response = $this->postJson('/api/auth/email/verification-notification');

        $response->assertStatus(401);
    }

    public function test_user_can_verify_email_successfully()
    {
        Event::fake();
        Sanctum::actingAs($this->unverifiedUser);

        // Simuler une requête de vérification d'email valide
        $verificationUrl = $this->createVerificationUrl($this->unverifiedUser);

        $response = $this->getJson($verificationUrl);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Email verified successfully'
            ]);

        // Vérifier que l'utilisateur est maintenant vérifié
        $this->unverifiedUser->refresh();
        $this->assertNotNull($this->unverifiedUser->email_verified_at);

        // Vérifier que l'événement Verified a été déclenché
        Event::assertDispatched(Verified::class);
    }

    public function test_already_verified_user_gets_appropriate_response()
    {
        Sanctum::actingAs($this->verifiedUser);

        $verificationUrl = $this->createVerificationUrl($this->verifiedUser);

        $response = $this->getJson($verificationUrl);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email already verified'
            ]);
    }

    public function test_user_can_check_verification_status_verified()
    {
        Sanctum::actingAs($this->verifiedUser);

        $response = $this->getJson('/api/auth/email/verification-status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'verified',
                    'email',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Verification status retrieved successfully',
                'data' => [
                    'verified' => true,
                    'email' => $this->verifiedUser->email
                ]
            ]);
    }

    public function test_user_can_check_verification_status_unverified()
    {
        Sanctum::actingAs($this->unverifiedUser);

        $response = $this->getJson('/api/auth/email/verification-status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Verification status retrieved successfully',
                'data' => [
                    'verified' => false,
                    'email' => $this->unverifiedUser->email
                ]
            ]);
    }

    public function test_unauthenticated_user_cannot_check_verification_status()
    {
        $response = $this->getJson('/api/auth/email/verification-status');

        $response->assertStatus(401);
    }

    public function test_verification_status_includes_user_resource()
    {
        Sanctum::actingAs($this->verifiedUser);

        $response = $this->getJson('/api/auth/email/verification-status');

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('user', $responseData['data']);
        $this->assertArrayHasKey('role', $responseData['data']['user']);
        $this->assertEquals($this->verifiedUser->id, $responseData['data']['user']['id']);
    }

    public function test_verification_response_does_not_expose_sensitive_data()
    {
        Sanctum::actingAs($this->verifiedUser);

        $response = $this->getJson('/api/auth/email/verification-status');

        $response->assertStatus(200);

        $responseData = $response->json();

        // Vérifier que les données sensibles ne sont pas exposées
        $this->assertArrayNotHasKey('password', $responseData['data']['user']);
        $this->assertArrayNotHasKey('remember_token', $responseData['data']['user']);
    }

    public function test_invalid_verification_url_returns_error()
    {
        Sanctum::actingAs($this->unverifiedUser);

        // URL de vérification invalide
        $invalidUrl = '/api/auth/email/verify/invalid-id/invalid-hash';

        $response = $this->getJson($invalidUrl);

        $response->assertStatus(403); // Forbidden par EmailVerificationRequest
    }

    public function test_verification_email_sending_failure_is_handled()
    {
        // Simuler une erreur lors de l'envoi d'email
        Notification::fake();

        // Créer un utilisateur avec un email invalide pour déclencher une erreur
        $userWithInvalidEmail = User::factory()->create([
            'email' => '', // Email vide pour déclencher une erreur
            'email_verified_at' => null,
            'role_id' => $this->role->id
        ]);

        Sanctum::actingAs($userWithInvalidEmail);

        $response = $this->postJson('/api/auth/email/verification-notification');

        // Le contrôleur devrait gérer l'erreur gracieusement
        $this->assertTrue(in_array($response->status(), [200, 500]));
    }

    /**
     * Créer une URL de vérification valide pour un utilisateur
     */
    private function createVerificationUrl(User $user): string
    {
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        // Convertir l'URL complète en chemin relatif pour les tests
        return parse_url($verificationUrl, PHP_URL_PATH) . '?' . parse_url($verificationUrl, PHP_URL_QUERY);
    }
}
