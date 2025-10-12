<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les rôles de base pour les tests
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /** @test */
    public function it_can_be_created_with_name_and_description()
    {
        $role = Role::create([
            'name' => 'Test Role',
            'description' => 'Test description',
        ]);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('Test Role', $role->name);
        $this->assertEquals('Test description', $role->description);
    }

    /** @test */
    public function it_has_admin_privileges_correctly()
    {
        $adminRole = Role::where('name', 'Administrateur')->first();
        $churchAdminRole = Role::where('name', 'Administrateur d\'église')->first();
        $userRole = Role::where('name', 'Utilisateur')->first();
        $moderatorRole = Role::where('name', 'Modérateur')->first();

        $this->assertTrue($adminRole->hasAdminPrivileges());
        $this->assertTrue($churchAdminRole->hasAdminPrivileges());
        $this->assertFalse($userRole->hasAdminPrivileges());
        $this->assertFalse($moderatorRole->hasAdminPrivileges());
    }

    /** @test */
    public function it_can_moderate_correctly()
    {
        $adminRole = Role::where('name', 'Administrateur')->first();
        $moderatorRole = Role::where('name', 'Modérateur')->first();
        $userRole = Role::where('name', 'Utilisateur')->first();
        $churchAdminRole = Role::where('name', 'Administrateur d\'église')->first();

        $this->assertTrue($adminRole->canModerate());
        $this->assertTrue($moderatorRole->canModerate());
        $this->assertFalse($userRole->canModerate());
        $this->assertFalse($churchAdminRole->canModerate());
    }

    /** @test */
    public function it_can_manage_church_correctly()
    {
        $adminRole = Role::where('name', 'Administrateur')->first();
        $churchAdminRole = Role::where('name', 'Administrateur d\'église')->first();
        $userRole = Role::where('name', 'Utilisateur')->first();
        $moderatorRole = Role::where('name', 'Modérateur')->first();

        $this->assertTrue($adminRole->canManageChurch());
        $this->assertTrue($churchAdminRole->canManageChurch());
        $this->assertFalse($userRole->canManageChurch());
        $this->assertFalse($moderatorRole->canManageChurch());
    }

    /** @test */
    public function of_type_scope_filters_correctly()
    {
        $adminRoles = Role::ofType('Administrateur')->get();
        $userRoles = Role::ofType('Utilisateur')->get();

        $this->assertEquals(1, $adminRoles->count());
        $this->assertEquals('Administrateur', $adminRoles->first()->name);

        foreach ($userRoles as $userRole) {
            $this->assertEquals('Utilisateur', $userRole->name);
        }
    }

    /** @test */
    public function get_by_name_returns_correct_role()
    {
        $adminRole = Role::getByName('Administrateur');
        $nonExistentRole = Role::getByName('Non Existant');

        $this->assertInstanceOf(Role::class, $adminRole);
        $this->assertEquals('Administrateur', $adminRole->name);
        $this->assertNull($nonExistentRole);
    }

    /** @test */
    public function is_type_checks_role_correctly()
    {
        $adminRole = Role::where('name', 'Administrateur')->first();
        $userRole = Role::where('name', 'Utilisateur')->first();

        $this->assertTrue($adminRole->isType('Administrateur'));
        $this->assertFalse($adminRole->isType('Utilisateur'));

        $this->assertTrue($userRole->isType('Utilisateur'));
        $this->assertFalse($userRole->isType('Administrateur'));
    }

    /** @test */
    public function it_has_users_relationship()
    {
        $role = Role::where('name', 'Utilisateur')->first();

        // Créer un utilisateur avec ce rôle
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($role->users()->exists());
        $this->assertEquals($user->id, $role->users->first()->id);
    }

    /** @test */
    public function all_default_roles_are_created_by_seeder()
    {
        $expectedRoles = [
            'Utilisateur',
            'Administrateur',
            'Administrateur d\'église',
            'Modérateur',
        ];

        foreach ($expectedRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            $this->assertNotNull($role, "Role {$roleName} should exist");
            $this->assertNotEmpty($role->name);
            $this->assertNotEmpty($role->description);
        }
    }

    /** @test */
    public function roles_have_unique_names()
    {
        $adminRole1 = Role::where('name', 'Administrateur')->first();
        $adminRole2 = Role::where('name', 'Administrateur')->get();

        $this->assertEquals(1, $adminRole2->count());
        $this->assertInstanceOf(Role::class, $adminRole1);
    }
}
