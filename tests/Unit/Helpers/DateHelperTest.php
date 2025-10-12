<?php

namespace Tests\Unit\Helpers;

use App\Helpers\DateHelper;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class DateHelperTest extends TestCase
{
    /** @test */
    public function it_returns_null_for_null_date()
    {
        $this->assertNull(DateHelper::timeAgo(null));
    }

    /** @test */
    public function it_formats_seconds_correctly()
    {
        $now = Carbon::now();
        $date = $now->copy()->subSeconds(30);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('il y a 30 secondes', $result);
    }

    /** @test */
    public function it_formats_single_second_correctly()
    {
        $now = Carbon::now();
        $date = $now->copy()->subSeconds(1);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('il y a 1 seconde', $result);
    }

    /** @test */
    public function it_formats_minutes_correctly()
    {
        $now = Carbon::now();
        $date = $now->copy()->subMinutes(15);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('il y a 15 minutes', $result);
    }

    /** @test */
    public function it_formats_single_minute_correctly()
    {
        $now = Carbon::now();
        $date = $now->copy()->subMinutes(1);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('il y a 1 minute', $result);
    }

    /** @test */
    public function it_formats_hours_correctly()
    {
        $now = Carbon::now();
        $date = $now->copy()->subHours(5);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('il y a 5 heures', $result);
    }

    /** @test */
    public function it_formats_single_hour_correctly()
    {
        $now = Carbon::now();
        $date = $now->copy()->subHours(1);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('il y a 1 heure', $result);
    }

    /** @test */
    public function it_formats_days_correctly()
    {
        $now = Carbon::now();
        $date = $now->copy()->subDays(10);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('il y a 10 jours', $result);
    }

    /** @test */
    public function it_formats_single_day_correctly()
    {
        $now = Carbon::now();
        $date = $now->copy()->subDays(1);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('il y a 1 jour', $result);
    }

    /** @test */
    public function it_formats_months_correctly()
    {
        $now = Carbon::now();
        $date = $now->copy()->subMonths(6);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('il y a 6 mois', $result);
    }

    /** @test */
    public function it_formats_single_month_correctly()
    {
        $now = Carbon::now();
        $date = $now->copy()->subMonths(1);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('il y a 1 mois', $result);
    }

    /** @test */
    public function it_formats_years_correctly()
    {
        $now = Carbon::now();
        $date = $now->copy()->subYears(3);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('il y a 3 ans', $result);
    }

    /** @test */
    public function it_formats_single_year_correctly()
    {
        $now = Carbon::now();
        $date = $now->copy()->subYears(1);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('il y a 1 an', $result);
    }

    /** @test */
    public function it_handles_future_dates()
    {
        $now = Carbon::now();
        $date = $now->copy()->addMinutes(30);

        $result = DateHelper::timeAgo($date, $now);

        $this->assertEquals('dans 30 minutes', $result);
    }

    /** @test */
    public function it_formats_french_date_correctly()
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', '2025-03-15 14:30:45');

        $result = DateHelper::formatFrench($date, 'd/m/Y à H:i');

        $this->assertEquals('15/03/2025 à 14:30', $result);
    }

    /** @test */
    public function it_returns_correct_day_name_in_french()
    {
        // Lundi = 1 (selon Carbon)
        $monday = Carbon::createFromFormat('Y-m-d', '2025-01-06'); // Un lundi

        $result = DateHelper::getDayNameFrench($monday);

        $this->assertEquals('lundi', $result);
    }

    /** @test */
    public function it_returns_correct_month_name_in_french()
    {
        $march = Carbon::createFromFormat('Y-m-d', '2025-03-15');

        $result = DateHelper::getMonthNameFrench($march);

        $this->assertEquals('mars', $result);
    }

    /** @test */
    public function it_formats_full_french_date_correctly()
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', '2025-03-15 14:30:45');
        // 15 mars 2025 est un samedi

        $result = DateHelper::formatFullFrench($date);

        $this->assertEquals('samedi 15 mars 2025 à 14:30', $result);
    }

    /** @test */
    public function it_handles_relative_dates()
    {
        $now = Carbon::now();

        // Aujourd'hui
        $today = $now->copy()->setTime(10, 30);
        $result = DateHelper::formatRelative($today);
        $this->assertEquals("aujourd'hui à 10:30", $result);

        // Hier
        $yesterday = $now->copy()->subDay()->setTime(15, 45);
        $result = DateHelper::formatRelative($yesterday);
        $this->assertEquals('hier à 15:45', $result);

        // Demain
        $tomorrow = $now->copy()->addDay()->setTime(9, 15);
        $result = DateHelper::formatRelative($tomorrow);
        $this->assertEquals('demain à 09:15', $result);
    }

    /** @test */
    public function it_handles_string_dates()
    {
        $dateString = '2025-01-01 12:00:00';
        $referenceDate = Carbon::createFromFormat('Y-m-d H:i:s', '2025-01-02 12:00:00');

        $result = DateHelper::timeAgo($dateString, $referenceDate);

        $this->assertEquals('il y a 1 jour', $result);
    }
}
