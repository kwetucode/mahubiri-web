<?php

namespace Tests\Unit\Enums;

use App\Enums\RoleType;
use PHPUnit\Framework\TestCase;

class RoleTypeTest extends TestCase
{
    /** @test */
    public function it_has_correct_role_constants()
    {
        $this->assertEquals(0, RoleType::USER);
        $this->assertEquals(1, RoleType::ADMIN);
        $this->assertEquals(2, RoleType::CHURCH_ADMIN);
        $this->assertEquals(3, RoleType::MODERATOR);
    }

    /** @test */
    public function it_returns_correct_french_descriptions()
    {
        $this->assertEquals('Utilisateur', RoleType::getDescription(RoleType::USER));
        $this->assertEquals('Administrateur', RoleType::getDescription(RoleType::ADMIN));
        $this->assertEquals('Administrateur d\'église', RoleType::getDescription(RoleType::CHURCH_ADMIN));
        $this->assertEquals('Modérateur', RoleType::getDescription(RoleType::MODERATOR));
    }

    /** @test */
    public function it_returns_all_roles_with_descriptions()
    {
        $expected = [
            RoleType::USER => 'Utilisateur',
            RoleType::ADMIN => 'Administrateur',
            RoleType::CHURCH_ADMIN => 'Administrateur d\'église',
            RoleType::MODERATOR => 'Modérateur',
        ];

        $this->assertEquals($expected, RoleType::getRolesWithDescriptions());
    }

    /** @test */
    public function it_correctly_identifies_admin_privileges()
    {
        // Rôles avec privilèges admin
        $this->assertTrue(RoleType::hasAdminPrivileges(RoleType::ADMIN));
        $this->assertTrue(RoleType::hasAdminPrivileges(RoleType::CHURCH_ADMIN));

        // Rôles sans privilèges admin
        $this->assertFalse(RoleType::hasAdminPrivileges(RoleType::USER));
        $this->assertFalse(RoleType::hasAdminPrivileges(RoleType::MODERATOR));
    }

    /** @test */
    public function it_correctly_identifies_moderation_privileges()
    {
        // Rôles qui peuvent modérer
        $this->assertTrue(RoleType::canModerate(RoleType::ADMIN));
        $this->assertTrue(RoleType::canModerate(RoleType::MODERATOR));

        // Rôles qui ne peuvent pas modérer
        $this->assertFalse(RoleType::canModerate(RoleType::USER));
        $this->assertFalse(RoleType::canModerate(RoleType::CHURCH_ADMIN));
    }

    /** @test */
    public function it_correctly_identifies_church_management_privileges()
    {
        // Rôles qui peuvent gérer une église
        $this->assertTrue(RoleType::canManageChurch(RoleType::ADMIN));
        $this->assertTrue(RoleType::canManageChurch(RoleType::CHURCH_ADMIN));

        // Rôles qui ne peuvent pas gérer une église
        $this->assertFalse(RoleType::canManageChurch(RoleType::USER));
        $this->assertFalse(RoleType::canManageChurch(RoleType::MODERATOR));
    }

    /** @test */
    public function it_handles_invalid_role_values_gracefully()
    {
        // Test avec une valeur invalide
        $invalidRole = 999;

        $this->assertFalse(RoleType::hasAdminPrivileges($invalidRole));
        $this->assertFalse(RoleType::canModerate($invalidRole));
        $this->assertFalse(RoleType::canManageChurch($invalidRole));
    }

    /** @test */
    public function roles_have_distinct_privileges()
    {
        // USER : aucun privilège spécial
        $this->assertFalse(RoleType::hasAdminPrivileges(RoleType::USER));
        $this->assertFalse(RoleType::canModerate(RoleType::USER));
        $this->assertFalse(RoleType::canManageChurch(RoleType::USER));

        // ADMIN : tous les privilèges
        $this->assertTrue(RoleType::hasAdminPrivileges(RoleType::ADMIN));
        $this->assertTrue(RoleType::canModerate(RoleType::ADMIN));
        $this->assertTrue(RoleType::canManageChurch(RoleType::ADMIN));

        // CHURCH_ADMIN : peut gérer église mais pas modérer
        $this->assertTrue(RoleType::hasAdminPrivileges(RoleType::CHURCH_ADMIN));
        $this->assertFalse(RoleType::canModerate(RoleType::CHURCH_ADMIN));
        $this->assertTrue(RoleType::canManageChurch(RoleType::CHURCH_ADMIN));

        // MODERATOR : peut modérer mais pas gérer église
        $this->assertFalse(RoleType::hasAdminPrivileges(RoleType::MODERATOR));
        $this->assertTrue(RoleType::canModerate(RoleType::MODERATOR));
        $this->assertFalse(RoleType::canManageChurch(RoleType::MODERATOR));
    }

    /** @test */
    public function it_can_be_used_with_enum_methods()
    {
        // Test des méthodes statiques de l'enum
        $userRole = RoleType::USER();
        $adminRole = RoleType::ADMIN();

        $this->assertInstanceOf(RoleType::class, $userRole);
        $this->assertInstanceOf(RoleType::class, $adminRole);

        $this->assertEquals(RoleType::USER, $userRole->value);
        $this->assertEquals(RoleType::ADMIN, $adminRole->value);
    }

    /** @test */
    public function roles_can_be_compared()
    {
        $this->assertNotEquals(RoleType::USER, RoleType::ADMIN);
        $this->assertNotEquals(RoleType::CHURCH_ADMIN, RoleType::MODERATOR);

        $this->assertEquals(RoleType::USER, RoleType::USER);
        $this->assertEquals(RoleType::ADMIN, RoleType::ADMIN);
    }

    /** @test */
    public function all_roles_have_valid_descriptions()
    {
        $roles = [RoleType::USER, RoleType::ADMIN, RoleType::CHURCH_ADMIN, RoleType::MODERATOR];

        foreach ($roles as $role) {
            $description = RoleType::getDescription($role);
            $this->assertIsString($description);
            $this->assertNotEmpty($description);
            $this->assertStringNotContainsString('OptionOne', $description); // S'assurer qu'on n'a pas les anciens noms
        }
    }
}
