<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule sermon popularity calculation
Schedule::command('sermons:calculate-popularity')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        info('Sermon popularity calculation completed successfully');
    })
    ->onFailure(function () {
        logger()->error('Sermon popularity calculation failed');
    });
