<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    echo Inspiring::quote().PHP_EOL;
})->purpose('Display an inspiring quote');

Schedule::command('posts:publish-scheduled')->everyMinute()->withoutOverlapping();
