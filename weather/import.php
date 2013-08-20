<?php

/**
 * TODO: replace this manual import script with curl based service that runs either per 
 * request or on a cron job and fetches live data from the noaa server. 
 */

require_once __DIR__.'/lib/Model.php';
require_once __DIR__.'/lib/WeatherService.php';

if (count($argv) != 2) {
    echo "\nUsage:  php import.php <filepath>\n\n";
    exit;
}

$datasetpath = $argv[1];

WeatherService::importDataset($datasetpath);