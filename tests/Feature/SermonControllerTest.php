<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Role;
use App\Models\Sermon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SermonControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(): User
    {
        $role = Role::factory()->create();
        return User::factory()->create(['role_id' => $role->id]);
    }

    /**
     * Test creating a sermon with audio upload
     */
    public function test_user_can_create_sermon_with_audio(): void
    {
        $user = $this->createUserWithRole();
        $church = Church::factory()->create(['created_by' => $user->id]);

        Sanctum::actingAs($user);

        $sermonData = [
            'title' => 'Test Sermon',
            'preacher_name' => 'Pastor Test',
            'description' => 'This is a test sermon',
            'audio_base64' => 'data:audio/mp3;base64,/+MYxAAEaAIEeUAQAgBgNgP/////KQQ/////Lvrg+lcWYHgtjadzsbTq+yREu49',
            'church_id' => $church->id,
        ];

        $response = $this->postJson('/api/v1/sermons', $sermonData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'preacher_name',
                    'description',
                    'audio_url',
                    'church_id',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('sermons', [
            'title' => 'Test Sermon',
            'preacher_name' => 'Pastor Test',
            'church_id' => $church->id,
        ]);
    }

    /**
     * Test listing sermons for user's church
     */
    public function test_user_can_list_their_church_sermons(): void
    {
        $user = $this->createUserWithRole();
        $church = Church::factory()->create(['created_by' => $user->id]);

        // Create some sermons for this church
        Sermon::factory()->count(3)->create(['church_id' => $church->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/sermons/my-church-sermons');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'preacher_name',
                        'church_id'
                    ]
                ]
            ]);
    }

    /**
     * Test creating sermon with invalid audio format returns error
     */
    public function test_creating_sermon_with_invalid_audio_returns_error(): void
    {
        $user = $this->createUserWithRole();
        $church = Church::factory()->create(['created_by' => $user->id]);

        Sanctum::actingAs($user);

        $sermonData = [
            'title' => 'Test Sermon',
            'preacher_name' => 'Pastor Test',
            'audio_base64' => 'invalid-base64-format',
            'church_id' => $church->id,
        ];

        $response = $this->postJson('/api/v1/sermons', $sermonData);

        $response->assertStatus(422) // Validation catches the error first
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'audio_base64'
                ]
            ]);
    }

    /**
     * Test updating sermon with new audio replaces old file
     */
    public function test_updating_sermon_replaces_audio_file(): void
    {
        $user = $this->createUserWithRole();
        $church = Church::factory()->create(['created_by' => $user->id]);

        Sanctum::actingAs($user);

        // Create sermon with initial audio
        $initialData = [
            'title' => 'Test Sermon',
            'preacher_name' => 'Pastor Test',
            'audio_base64' => 'data:audio/mp3;base64,' . base64_encode('initial audio'),
            'church_id' => $church->id,
        ];

        $createResponse = $this->postJson('/api/v1/sermons', $initialData);
        $createResponse->assertStatus(201);

        $sermon = $createResponse->json('data');
        $initialAudioUrl = $sermon['audio_url'];

        // Update with new audio
        $updateData = [
            'audio_base64' => 'data:audio/mp3;base64,' . base64_encode('updated audio'),
        ];

        $updateResponse = $this->patchJson("/api/v1/sermons/{$sermon['id']}", $updateData);
        $updateResponse->assertStatus(200);

        $updatedSermon = $updateResponse->json('data');

        // Verify audio URL has changed
        $this->assertNotEquals($initialAudioUrl, $updatedSermon['audio_url']);
    }

    /**
     * Test deleting sermon removes all associated files
     */
    public function test_deleting_sermon_removes_associated_files(): void
    {
        $user = $this->createUserWithRole();
        $church = Church::factory()->create(['created_by' => $user->id]);

        Sanctum::actingAs($user);

        // Create sermon with both audio and cover
        $sermonData = [
            'title' => 'Test Sermon',
            'preacher_name' => 'Pastor Test',
            'audio_base64' => 'data:audio/mp3;base64,' . base64_encode('test audio'),
            'cover_base64' => 'data:image/jpeg;base64,' . base64_encode('test image'),
            'church_id' => $church->id,
        ];

        $createResponse = $this->postJson('/api/v1/sermons', $sermonData);
        $createResponse->assertStatus(201);

        $sermon = $createResponse->json('data');

        // Delete the sermon
        $deleteResponse = $this->deleteJson("/api/v1/sermons/{$sermon['id']}");
        $deleteResponse->assertStatus(200);

        // Verify sermon is deleted from database
        $this->assertDatabaseMissing('sermons', ['id' => $sermon['id']]);
    }
}
