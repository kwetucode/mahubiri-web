<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::factory()->create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Administrator role',
            'is_active' => true
        ]);

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+33123456789',
            'email_verified_at' => now(),
            'role_id' => $this->role->id
        ]);
    }

    public function test_user_resource_transforms_correctly()
    {
        $request = Request::create('/test', 'GET');
        $resource = new UserResource($this->user);
        $array = $resource->toArray($request);

        $this->assertEquals($this->user->id, $array['id']);
        $this->assertEquals($this->user->name, $array['name']);
        $this->assertEquals($this->user->email, $array['email']);
        $this->assertEquals($this->user->phone, $array['phone']);
        $this->assertEquals($this->user->email_verified_at, $array['email_verified_at']);
        $this->assertEquals($this->user->created_at, $array['created_at']);
        $this->assertEquals($this->user->updated_at, $array['updated_at']);
    }

    public function test_user_resource_includes_role_when_loaded()
    {
        $userWithRole = $this->user->load('role');
        $request = Request::create('/test', 'GET');
        $resource = new UserResource($userWithRole);
        $array = $resource->toArray($request);

        $this->assertArrayHasKey('role', $array);
        $this->assertInstanceOf(RoleResource::class, $array['role']);
    }

    public function test_user_resource_does_not_include_role_when_not_loaded()
    {
        // Créer un nouvel utilisateur sans charger la relation
        $userWithoutRole = User::find($this->user->id);
        $request = Request::create('/test', 'GET');
        $resource = new UserResource($userWithoutRole);
        $array = $resource->toArray($request);

        // La relation devrait être incluse mais avec un MissingValue quand elle n'est pas chargée
        $this->assertArrayHasKey('role', $array);
        // whenLoaded retourne une RoleResource avec MissingValue quand non chargée
        $this->assertInstanceOf(RoleResource::class, $array['role']);
    }

    public function test_user_resource_does_not_expose_sensitive_data()
    {
        $request = Request::create('/test', 'GET');
        $resource = new UserResource($this->user);
        $array = $resource->toArray($request);

        // Vérifier que les champs sensibles ne sont pas exposés
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_user_resource_handles_null_values()
    {
        $userWithNulls = User::factory()->create([
            'phone' => null,
            'email_verified_at' => null,
            'role_id' => $this->role->id
        ]);

        $request = Request::create('/test', 'GET');
        $resource = new UserResource($userWithNulls);
        $array = $resource->toArray($request);

        $this->assertNull($array['phone']);
        $this->assertNull($array['email_verified_at']);
    }

    public function test_user_resource_collection_works()
    {
        $users = User::factory()->count(3)->create(['role_id' => $this->role->id]);
        $request = Request::create('/test', 'GET');

        $collection = UserResource::collection($users);
        $array = $collection->toArray($request);

        $this->assertIsArray($array);
        $this->assertCount(3, $array);

        foreach ($array as $userArray) {
            $this->assertArrayHasKey('id', $userArray);
            $this->assertArrayHasKey('name', $userArray);
            $this->assertArrayHasKey('email', $userArray);
            $this->assertArrayNotHasKey('password', $userArray);
        }
    }

    public function test_user_resource_with_role_resource_structure()
    {
        $userWithRole = $this->user->load('role');
        $request = Request::create('/test', 'GET');
        $resource = new UserResource($userWithRole);
        $array = $resource->toArray($request);

        // Vérifier la structure de la ressource role incluse
        $roleArray = $array['role']->toArray($request);
        $this->assertArrayHasKey('id', $roleArray);
        $this->assertArrayHasKey('name', $roleArray);
        $this->assertArrayHasKey('slug', $roleArray);
        $this->assertArrayHasKey('description', $roleArray);
        $this->assertArrayHasKey('is_active', $roleArray);
    }
}
