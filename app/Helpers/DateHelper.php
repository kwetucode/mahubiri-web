<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class DateHelper
{
    /**
     * Convertit une date en format "il y a X temps" en français
     *
     * @param Carbon|string|null $date
     * @param Carbon|null $referenceDate Date de référence (par défaut maintenant)
     * @return string|null
     */
    public static function timeAgo($date, ?Carbon $referenceDate = null): ?string
    {
        if (!$date) {
            return null;
        }

        // Convertir en Carbon si nécessaire
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $referenceDate = $referenceDate ?? now();

        // Déterminer si c'est dans le futur
        $isFuture = $date->gt($referenceDate);
        $prefix = $isFuture ? 'dans' : 'il y a';
        $suffix = $isFuture ? '' : '';

        // Calculer les différences absolues (toujours positives)
        $diffInSeconds = abs($date->diffInSeconds($referenceDate));
        $diffInMinutes = abs($date->diffInMinutes($referenceDate));
        $diffInHours = abs($date->diffInHours($referenceDate));
        $diffInDays = abs($date->diffInDays($referenceDate));
        $diffInMonths = abs($date->diffInMonths($referenceDate));
        $diffInYears = abs($date->diffInYears($referenceDate));

        // Logique de formatage
        if ($diffInSeconds < 60) {
            return self::formatTimeUnit($prefix, (int)$diffInSeconds, 'seconde', $suffix);
        }

        if ($diffInMinutes < 60) {
            return self::formatTimeUnit($prefix, (int)$diffInMinutes, 'minute', $suffix);
        }

        if ($diffInHours < 24) {
            return self::formatTimeUnit($prefix, (int)$diffInHours, 'heure', $suffix);
        }

        if ($diffInDays < 30) {
            return self::formatTimeUnit($prefix, (int)$diffInDays, 'jour', $suffix);
        }

        if ($diffInMonths < 12) {
            return self::formatTimeUnit($prefix, (int)$diffInMonths, 'mois', $suffix, false); // mois ne prend pas de 's'
        }

        return self::formatTimeUnit($prefix, (int)$diffInYears, 'an', $suffix);
    }

    /**
     * Formate une unité de temps avec les règles de pluriel français
     *
     * @param string $prefix
     * @param int $value
     * @param string $unit
     * @param string $suffix
     * @param bool $pluralize
     * @return string
     */
    private static function formatTimeUnit(string $prefix, int $value, string $unit, string $suffix = '', bool $pluralize = true): string
    {
        $pluralSuffix = '';

        if ($pluralize && $value > 1) {
            // Règles de pluriel français
            switch ($unit) {
                case 'an':
                    $pluralSuffix = 's';
                    break;
                case 'seconde':
                case 'minute':
                case 'heure':
                case 'jour':
                    $pluralSuffix = 's';
                    break;
                case 'mois':
                    // "mois" ne change pas au pluriel
                    $pluralSuffix = '';
                    break;
            }
        }

        return trim("{$prefix} {$value} {$unit}{$pluralSuffix} {$suffix}");
    }

    /**
     * Formate une date en format français lisible
     *
     * @param Carbon|string|null $date
     * @param string $format
     * @return string|null
     */
    public static function formatFrench($date, string $format = 'd/m/Y à H:i'): ?string
    {
        if (!$date) {
            return null;
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        return $date->format($format);
    }

    /**
     * Formate une date en format relatif français (aujourd'hui, hier, demain)
     *
     * @param Carbon|string|null $date
     * @return string|null
     */
    public static function formatRelative($date): ?string
    {
        if (!$date) {
            return null;
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $now = now();

        if ($date->isToday()) {
            return "aujourd'hui à " . $date->format('H:i');
        }

        if ($date->isYesterday()) {
            return 'hier à ' . $date->format('H:i');
        }

        if ($date->isTomorrow()) {
            return 'demain à ' . $date->format('H:i');
        }

        // Pour les autres dates, utiliser timeAgo
        return self::timeAgo($date);
    }

    /**
     * Obtient le nom du jour en français
     *
     * @param Carbon|string|null $date
     * @return string|null
     */
    public static function getDayNameFrench($date): ?string
    {
        if (!$date) {
            return null;
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $days = [
            0 => 'dimanche',
            1 => 'lundi',
            2 => 'mardi',
            3 => 'mercredi',
            4 => 'jeudi',
            5 => 'vendredi',
            6 => 'samedi'
        ];

        return $days[$date->dayOfWeek];
    }

    /**
     * Obtient le nom du mois en français
     *
     * @param Carbon|string|null $date
     * @return string|null
     */
    public static function getMonthNameFrench($date): ?string
    {
        if (!$date) {
            return null;
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $months = [
            1 => 'janvier',
            2 => 'février',
            3 => 'mars',
            4 => 'avril',
            5 => 'mai',
            6 => 'juin',
            7 => 'juillet',
            8 => 'août',
            9 => 'septembre',
            10 => 'octobre',
            11 => 'novembre',
            12 => 'décembre'
        ];

        return $months[$date->month];
    }

    /**
     * Formate une date complète en français
     *
     * @param Carbon|string|null $date
     * @return string|null
     */
    public static function formatFullFrench($date): ?string
    {
        if (!$date) {
            return null;
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $dayName = self::getDayNameFrench($date);
        $monthName = self::getMonthNameFrench($date);

        return "{$dayName} {$date->day} {$monthName} {$date->year} à {$date->format('H:i')}";
    }
}
