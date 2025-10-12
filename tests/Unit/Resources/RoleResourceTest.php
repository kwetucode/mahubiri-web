<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class RoleResourceTest extends TestCase
{
    use RefreshDatabase;

    protected $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::factory()->create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Administrator role with full access',
            'is_active' => true
        ]);
    }

    public function test_role_resource_transforms_correctly()
    {
        $request = Request::create('/test', 'GET');
        $resource = new RoleResource($this->role);
        $array = $resource->toArray($request);

        $this->assertEquals($this->role->id, $array['id']);
        $this->assertEquals($this->role->name, $array['name']);
        $this->assertEquals($this->role->slug, $array['slug']);
        $this->assertEquals($this->role->description, $array['description']);
        $this->assertEquals($this->role->is_active, $array['is_active']);
        $this->assertEquals($this->role->created_at, $array['created_at']);
        $this->assertEquals($this->role->updated_at, $array['updated_at']);
    }

    public function test_role_resource_includes_all_fields()
    {
        $request = Request::create('/test', 'GET');
        $resource = new RoleResource($this->role);
        $array = $resource->toArray($request);

        $expectedFields = [
            'id',
            'name',
            'slug',
            'description',
            'is_active',
            'created_at',
            'updated_at'
        ];

        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $array);
        }
    }

    public function test_role_resource_handles_boolean_values()
    {
        // Tester avec is_active = true
        $activeRole = Role::factory()->create(['is_active' => true]);
        $request = Request::create('/test', 'GET');
        $resource = new RoleResource($activeRole);
        $array = $resource->toArray($request);

        $this->assertTrue($array['is_active']);
        $this->assertIsBool($array['is_active']);

        // Tester avec is_active = false
        $inactiveRole = Role::factory()->create(['is_active' => false]);
        $resource = new RoleResource($inactiveRole);
        $array = $resource->toArray($request);

        $this->assertFalse($array['is_active']);
        $this->assertIsBool($array['is_active']);
    }

    public function test_role_resource_handles_null_description()
    {
        $roleWithoutDescription = Role::factory()->create([
            'description' => null
        ]);

        $request = Request::create('/test', 'GET');
        $resource = new RoleResource($roleWithoutDescription);
        $array = $resource->toArray($request);

        $this->assertNull($array['description']);
        $this->assertArrayHasKey('description', $array);
    }

    public function test_role_resource_collection_works()
    {
        $roles = Role::factory()->count(5)->create();
        $request = Request::create('/test', 'GET');

        $collection = RoleResource::collection($roles);
        $array = $collection->toArray($request);

        $this->assertIsArray($array);
        $this->assertCount(5, $array);

        foreach ($array as $roleArray) {
            $this->assertArrayHasKey('id', $roleArray);
            $this->assertArrayHasKey('name', $roleArray);
            $this->assertArrayHasKey('slug', $roleArray);
            $this->assertArrayHasKey('description', $roleArray);
            $this->assertArrayHasKey('is_active', $roleArray);
            $this->assertArrayHasKey('created_at', $roleArray);
            $this->assertArrayHasKey('updated_at', $roleArray);
        }
    }

    public function test_role_resource_data_types()
    {
        $request = Request::create('/test', 'GET');
        $resource = new RoleResource($this->role);
        $array = $resource->toArray($request);

        // Vérifier les types de données
        $this->assertIsInt($array['id']);
        $this->assertIsString($array['name']);
        $this->assertIsString($array['slug']);
        $this->assertIsBool($array['is_active']);

        // Vérifier que les timestamps sont des objets Carbon/DateTime
        $this->assertNotNull($array['created_at']);
        $this->assertNotNull($array['updated_at']);
    }

    public function test_role_resource_preserves_original_values()
    {
        $roleData = [
            'name' => 'Custom Role Name',
            'slug' => 'custom-role',
            'description' => 'This is a custom role description',
            'is_active' => false
        ];

        $customRole = Role::factory()->create($roleData);
        $request = Request::create('/test', 'GET');
        $resource = new RoleResource($customRole);
        $array = $resource->toArray($request);

        $this->assertEquals($roleData['name'], $array['name']);
        $this->assertEquals($roleData['slug'], $array['slug']);
        $this->assertEquals($roleData['description'], $array['description']);
        $this->assertEquals($roleData['is_active'], $array['is_active']);
    }

    public function test_role_resource_does_not_include_extra_fields()
    {
        $request = Request::create('/test', 'GET');
        $resource = new RoleResource($this->role);
        $array = $resource->toArray($request);

        // Vérifier qu'aucun champ supplémentaire n'est inclus
        $allowedFields = [
            'id',
            'name',
            'slug',
            'description',
            'is_active',
            'created_at',
            'updated_at'
        ];

        foreach (array_keys($array) as $field) {
            $this->assertContains($field, $allowedFields, "Field '$field' should not be included in RoleResource");
        }
    }

    public function test_role_resource_json_serialization()
    {
        $request = Request::create('/test', 'GET');
        $resource = new RoleResource($this->role);

        // Tester la sérialisation JSON
        $json = json_encode($resource->toArray($request));
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertEquals($this->role->id, $decoded['id']);
        $this->assertEquals($this->role->name, $decoded['name']);
    }
}
