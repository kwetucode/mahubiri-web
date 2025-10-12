<?php

// Test simple pour débugger le problème DateHelper
require_once 'vendor/autoload.php';

use App\Helpers\DateHelper;
use Carbon\Carbon;

echo "=== DEBUG DateHelper ===" . PHP_EOL;

$now = Carbon::now();
$future = $now->copy()->addMinutes(30);

echo "Now: " . $now->format('Y-m-d H:i:s') . PHP_EOL;
echo "Future: " . $future->format('Y-m-d H:i:s') . PHP_EOL;
echo "isFuture: " . ($future->gt($now) ? 'true' : 'false') . PHP_EOL;
echo "diffInSeconds: " . $future->diffInSeconds($now) . PHP_EOL;
echo "diffInMinutes: " . $future->diffInMinutes($now) . PHP_EOL;

// Test des méthodes Carbon pour comprendre
echo "secondsUntil: " . $now->diffInSeconds($future, false) . PHP_EOL;
echo "minutesUntil: " . $now->diffInMinutes($future, false) . PHP_EOL;

echo "timeAgo result: '" . DateHelper::timeAgo($future, $now) . "'" . PHP_EOL;
