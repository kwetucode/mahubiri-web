<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static USER()
 * @method static static ADMIN()
 * @method static static CHURCH_ADMIN()
 * @method static static MODERATOR()
 */
final class RoleType extends Enum
{
    const USER = 'user';
    const ADMIN = 'admin';
    const CHURCH_ADMIN = 'church_admin';
    const MODERATOR = 'moderator';

    /**
     * Get the description for display
     */
    public static function getDescription($value): string
    {
        switch ($value) {
            case self::USER:
                return 'Utilisateur';
            case self::ADMIN:
                return 'Administrateur';
            case self::CHURCH_ADMIN:
                return 'Administrateur d\'église';
            case self::MODERATOR:
                return 'Modérateur';
            default:
                return parent::getDescription($value);
        }
    }

    /**
     * Get all roles with their descriptions
     */
    public static function getRolesWithDescriptions(): array
    {
        return [
            self::USER => self::getDescription(self::USER),
            self::ADMIN => self::getDescription(self::ADMIN),
            self::CHURCH_ADMIN => self::getDescription(self::CHURCH_ADMIN),
            self::MODERATOR => self::getDescription(self::MODERATOR),
        ];
    }

    /**
     * Check if the role has admin privileges
     */
    public static function hasAdminPrivileges($role): bool
    {
        return in_array($role, [self::ADMIN, self::CHURCH_ADMIN]);
    }

    /**
     * Check if the role can moderate content
     */
    public static function canModerate($role): bool
    {
        return in_array($role, [self::ADMIN, self::MODERATOR]);
    }

    /**
     * Check if the role can manage church content
     */
    public static function canManageChurch($role): bool
    {
        return in_array($role, [self::ADMIN, self::CHURCH_ADMIN]);
    }
}
