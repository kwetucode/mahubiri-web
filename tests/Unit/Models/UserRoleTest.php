<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les rôles de base pour les tests
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /** @test */
    public function user_can_have_admin_role()
    {
        $adminRole = Role::where('name', 'Administrateur')->first();
        $user = User::factory()->create(['role_id' => $adminRole->id]);

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->hasAdminPrivileges());
        $this->assertTrue($user->canModerate());
        $this->assertTrue($user->canManageChurch());
        $this->assertEquals('Administrateur', $user->getRoleName());
    }

    /** @test */
    public function user_can_have_church_admin_role()
    {
        $churchAdminRole = Role::where('name', 'Administrateur d\'église')->first();
        $user = User::factory()->create(['role_id' => $churchAdminRole->id]);

        $this->assertTrue($user->isChurchAdmin());
        $this->assertTrue($user->hasAdminPrivileges());
        $this->assertFalse($user->canModerate());
        $this->assertTrue($user->canManageChurch());
        $this->assertEquals('Administrateur d\'église', $user->getRoleName());
    }

    /** @test */
    public function user_can_have_moderator_role()
    {
        $moderatorRole = Role::where('name', 'Modérateur')->first();
        $user = User::factory()->create(['role_id' => $moderatorRole->id]);

        $this->assertTrue($user->isModerator());
        $this->assertFalse($user->hasAdminPrivileges());
        $this->assertTrue($user->canModerate());
        $this->assertFalse($user->canManageChurch());
        $this->assertEquals('Modérateur', $user->getRoleName());
    }

    /** @test */
    public function user_can_have_standard_user_role()
    {
        $userRole = Role::where('name', 'Utilisateur')->first();
        $user = User::factory()->create(['role_id' => $userRole->id]);

        $this->assertTrue($user->isUser());
        $this->assertFalse($user->hasAdminPrivileges());
        $this->assertFalse($user->canModerate());
        $this->assertFalse($user->canManageChurch());
        $this->assertEquals('Utilisateur', $user->getRoleName());
    }

    /** @test */
    public function user_without_role_has_no_privileges()
    {
        $user = User::factory()->create(['role_id' => null]);

        $this->assertFalse($user->hasAdminPrivileges());
        $this->assertFalse($user->canModerate());
        $this->assertFalse($user->canManageChurch());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isChurchAdmin());
        $this->assertFalse($user->isModerator());
        $this->assertFalse($user->isUser());
        $this->assertNull($user->getRoleName());
    }

    /** @test */
    public function has_role_method_works_correctly()
    {
        $adminRole = Role::where('name', 'Administrateur')->first();
        $userRole = Role::where('name', 'Utilisateur')->first();

        $adminUser = User::factory()->create(['role_id' => $adminRole->id]);
        $standardUser = User::factory()->create(['role_id' => $userRole->id]);

        $this->assertTrue($adminUser->hasRole('Administrateur'));
        $this->assertFalse($adminUser->hasRole('Utilisateur'));

        $this->assertTrue($standardUser->hasRole('Utilisateur'));
        $this->assertFalse($standardUser->hasRole('Administrateur'));
    }

    /** @test */
    public function user_role_relationship_works()
    {
        $adminRole = Role::where('name', 'Administrateur')->first();
        $user = User::factory()->create(['role_id' => $adminRole->id]);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals($adminRole->id, $user->role->id);
        $this->assertEquals('Administrateur', $user->role->name);
    }

    /** @test */
    public function multiple_users_can_have_same_role()
    {
        $userRole = Role::where('name', 'Utilisateur')->first();

        $user1 = User::factory()->create(['role_id' => $userRole->id]);
        $user2 = User::factory()->create(['role_id' => $userRole->id]);
        $user3 = User::factory()->create(['role_id' => $userRole->id]);

        $this->assertTrue($user1->isUser());
        $this->assertTrue($user2->isUser());
        $this->assertTrue($user3->isUser());

        $this->assertEquals(3, $userRole->users()->count());
    }

    /** @test */
    public function role_privilege_checks_are_consistent()
    {
        $roles = [
            'Utilisateur' => [
                'hasAdminPrivileges' => false,
                'canModerate' => false,
                'canManageChurch' => false,
            ],
            'Administrateur' => [
                'hasAdminPrivileges' => true,
                'canModerate' => true,
                'canManageChurch' => true,
            ],
            'Administrateur d\'église' => [
                'hasAdminPrivileges' => true,
                'canModerate' => false,
                'canManageChurch' => true,
            ],
            'Modérateur' => [
                'hasAdminPrivileges' => false,
                'canModerate' => true,
                'canManageChurch' => false,
            ],
        ];

        foreach ($roles as $roleName => $expectedPrivileges) {
            $role = Role::where('name', $roleName)->first();
            $user = User::factory()->create(['role_id' => $role->id]);

            $this->assertEquals(
                $expectedPrivileges['hasAdminPrivileges'],
                $user->hasAdminPrivileges(),
                "Admin privileges check failed for role: {$roleName}"
            );

            $this->assertEquals(
                $expectedPrivileges['canModerate'],
                $user->canModerate(),
                "Moderation privileges check failed for role: {$roleName}"
            );

            $this->assertEquals(
                $expectedPrivileges['canManageChurch'],
                $user->canManageChurch(),
                "Church management privileges check failed for role: {$roleName}"
            );
        }
    }
}
