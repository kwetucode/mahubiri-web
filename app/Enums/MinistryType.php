<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static PASTEUR()
 * @method static static APOTRE()
 * @method static static EVANGELISTE()
 * @method static static PROPHETE()
 * @method static static ENSEIGNANT()
 * @method static static DOCTEUR()
 */
final class MinistryType extends Enum
{
    const PASTEUR = 'pasteur';
    const APOTRE = 'apotre';
    const EVANGELISTE = 'evangeliste';
    const PROPHETE = 'prophete';
    const ENSEIGNANT = 'enseignant';
    const DOCTEUR = 'docteur';

    /**
     * Get the description for display
     */
    public static function getDescription($value): string
    {
        switch ($value) {
            case self::PASTEUR:
                return 'Pasteur';
            case self::APOTRE:
                return 'Apôtre';
            case self::EVANGELISTE:
                return 'Évangéliste';
            case self::PROPHETE:
                return 'Prophète';
            case self::ENSEIGNANT:
                return 'Enseignant';
            case self::DOCTEUR:
                return 'Docteur';
            default:
                return parent::getDescription($value);
        }
    }

    /**
     * Get all ministry types with their descriptions
     */
    public static function getTypesWithDescriptions(): array
    {
        return [
            self::PASTEUR => self::getDescription(self::PASTEUR),
            self::APOTRE => self::getDescription(self::APOTRE),
            self::EVANGELISTE => self::getDescription(self::EVANGELISTE),
            self::PROPHETE => self::getDescription(self::PROPHETE),
            self::ENSEIGNANT => self::getDescription(self::ENSEIGNANT),
            self::DOCTEUR => self::getDescription(self::DOCTEUR),
        ];
    }

    /**
     * Get all ministry type values
     */
    public static function getMinistryValues(): array
    {
        return [
            self::PASTEUR,
            self::APOTRE,
            self::EVANGELISTE,
            self::PROPHETE,
            self::ENSEIGNANT,
            self::DOCTEUR,
        ];
    }
}
