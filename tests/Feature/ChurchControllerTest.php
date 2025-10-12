<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChurchControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function createUserWithRole()
    {
        $role = Role::factory()->create();
        return User::factory()->create(['role_id' => $role->id]);
    }

    /** @test */
    public function user_can_get_list_of_churches()
    {
        // Arrange
        $user = $this->createUserWithRole();

        $churchUsers = collect(range(1, 2))->map(fn() => $this->createUserWithRole());
        $churches = $churchUsers->map(function ($churchUser) {
            return Church::factory()->create(['created_by' => $churchUser->id]);
        });

        // Act
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/churches');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Churches retrieved successfully'
            ]);
    }

    /** @test */
    public function user_can_create_a_church()
    {
        // Arrange
        $user = $this->createUserWithRole();
        $churchData = [
            'name' => 'Test Church',
            'abbreviation' => 'TC',
            'description' => 'A test church description'
        ];

        // Act
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/churches', $churchData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Church created successfully',
            ]);

        // Assert database
        $this->assertDatabaseHas('churches', [
            'name' => 'Test Church',
            'created_by' => $user->id
        ]);
    }

    /** @test */
    public function user_cannot_create_multiple_churches()
    {
        // Arrange
        $user = $this->createUserWithRole();
        Church::factory()->create(['created_by' => $user->id]);

        $secondChurchData = [
            'name' => 'Second Church',
            'abbreviation' => 'SC'
        ];

        // Act
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/churches', $secondChurchData);

        // Assert
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Vous avez déjà créé une église. Un utilisateur ne peut créer qu\'une seule église.'
            ]);
    }

    /** @test */
    public function user_can_check_their_church_status()
    {
        // Arrange
        $user = $this->createUserWithRole();

        // Act - No church yet
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/churches/my-church');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_church' => false
            ]);
    }

    /** @test */
    public function church_name_is_required()
    {
        // Arrange
        $user = $this->createUserWithRole();

        // Act
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/churches', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
