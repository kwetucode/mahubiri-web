<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;

/**
 * Tests for Social Authentication
 * 
 * These tests demonstrate how to test the social authentication endpoints
 * 
 * To run these tests:
 * php artisan test --filter SocialAuthTest
 */
class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Mock a Socialite user response
     */
    protected function mockSocialiteUser($provider, $email, $name, $id)
    {
        $user = Mockery::mock('Laravel\Socialite\Two\User');
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('getEmail')->andReturn($email);
        $user->shouldReceive('getName')->andReturn($name);
        $user->shouldReceive('getNickname')->andReturn($name);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('userFromToken')->andReturn($user);

        Socialite::shouldReceive('driver')->andReturn($provider);
    }

    /**
     * Test successful Google login for new user
     */
    public function test_google_login_creates_new_user()
    {
        $this->mockSocialiteUser('google', 'test@gmail.com', 'Test User', 'google-123');

        $response = $this->postJson('/api/auth/social/login', [
            'provider' => 'google',
            'access_token' => 'fake-google-token',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'email_verified_at'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@gmail.com',
            'google_id' => 'google-123',
        ]);
    }

    /**
     * Test successful Facebook login for new user
     */
    public function test_facebook_login_creates_new_user()
    {
        $this->mockSocialiteUser('facebook', 'test@facebook.com', 'FB User', 'fb-456');

        $response = $this->postJson('/api/auth/social/login', [
            'provider' => 'facebook',
            'access_token' => 'fake-facebook-token',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@facebook.com',
            'facebook_id' => 'fb-456',
        ]);
    }

    /**
     * Test login with existing user (same email)
     */
    public function test_social_login_links_to_existing_user()
    {
        // Create a user with email but no social ID
        $user = User::factory()->create([
            'email' => 'existing@gmail.com',
        ]);

        $this->mockSocialiteUser('google', 'existing@gmail.com', 'Existing User', 'google-789');

        $response = $this->postJson('/api/auth/social/login', [
            'provider' => 'google',
            'access_token' => 'fake-google-token',
        ]);

        $response->assertStatus(200);

        // Check that the Google ID was linked to existing user
        $user->refresh();
        $this->assertEquals('google-789', $user->google_id);
    }

    /**
     * Test validation errors
     */
    public function test_social_login_validation_errors()
    {
        // Missing provider
        $response = $this->postJson('/api/auth/social/login', [
            'access_token' => 'fake-token',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['provider']);

        // Missing access_token
        $response = $this->postJson('/api/auth/social/login', [
            'provider' => 'google',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['access_token']);

        // Invalid provider
        $response = $this->postJson('/api/auth/social/login', [
            'provider' => 'twitter',
            'access_token' => 'fake-token',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['provider']);
    }

    /**
     * Test linking social account to authenticated user
     */
    public function test_authenticated_user_can_link_social_account()
    {
        $user = User::factory()->create();

        $this->mockSocialiteUser('facebook', 'test@facebook.com', 'FB User', 'fb-999');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/user/social/link', [
                'provider' => 'facebook',
                'access_token' => 'fake-facebook-token',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Facebook account linked successfully',
            ]);

        $user->refresh();
        $this->assertEquals('fb-999', $user->facebook_id);
    }

    /**
     * Test preventing linking already linked account
     */
    public function test_cannot_link_already_linked_social_account()
    {
        // Create first user with Facebook linked
        $user1 = User::factory()->create([
            'facebook_id' => 'fb-existing',
        ]);

        // Create second user trying to link same Facebook account
        $user2 = User::factory()->create();

        $this->mockSocialiteUser('facebook', 'test@facebook.com', 'FB User', 'fb-existing');

        $response = $this->actingAs($user2, 'sanctum')
            ->postJson('/api/user/social/link', [
                'provider' => 'facebook',
                'access_token' => 'fake-facebook-token',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'This Facebook account is already linked to another user',
            ]);
    }

    /**
     * Test unlinking social account
     */
    public function test_authenticated_user_can_unlink_social_account()
    {
        $user = User::factory()->create([
            'google_id' => 'google-123',
            'google_token' => 'some-token',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/user/social/unlink', [
                'provider' => 'google',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Google account unlinked successfully',
            ]);

        $user->refresh();
        $this->assertNull($user->google_id);
        $this->assertNull($user->google_token);
    }

    /**
     * Test cannot unlink non-linked account
     */
    public function test_cannot_unlink_non_linked_account()
    {
        $user = User::factory()->create([
            'google_id' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/user/social/unlink', [
                'provider' => 'google',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Google account is not linked',
            ]);
    }

    /**
     * Test getting social accounts status
     */
    public function test_get_social_accounts_status()
    {
        $user = User::factory()->create([
            'google_id' => 'google-123',
            'facebook_id' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user/social/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'google_linked' => true,
                    'facebook_linked' => false,
                ],
            ]);
    }

    /**
     * Test email is automatically verified for social accounts
     */
    public function test_social_account_email_is_verified()
    {
        $this->mockSocialiteUser('google', 'test@gmail.com', 'Test User', 'google-123');

        $this->postJson('/api/auth/social/login', [
            'provider' => 'google',
            'access_token' => 'fake-google-token',
        ]);

        $user = User::where('email', 'test@gmail.com')->first();

        $this->assertNotNull($user->email_verified_at);
    }

    /**
     * Test that social login requires authentication for protected routes
     */
    public function test_social_link_requires_authentication()
    {
        $response = $this->postJson('/api/user/social/link', [
            'provider' => 'google',
            'access_token' => 'fake-token',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that token is returned in correct format
     */
    public function test_social_login_returns_bearer_token()
    {
        $this->mockSocialiteUser('google', 'test@gmail.com', 'Test User', 'google-123');

        $response = $this->postJson('/api/auth/social/login', [
            'provider' => 'google',
            'access_token' => 'fake-google-token',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure([
                'data' => [
                    'token',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }
}
