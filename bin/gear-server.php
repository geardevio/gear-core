<?php

use GearDev\Core\Starter\Ignition;

const IS_GEAR_SERVER = true;

define('LARAVEL_START', microtime(true));

require_once __DIR__.'/../helpers/hack-load-helpers.php';
loadHacks(dirname($GLOBALS['_composer_autoload_path']).'/../');

file_put_contents(
    dirname($GLOBALS['_composer_autoload_path']).'/../artisan',
    '#!/usr/bin/env php' . PHP_EOL . '<?php' . PHP_EOL . 'require_once __DIR__ . \'/vendor/bin/gear.php\';' . PHP_EOL);
require_once $GLOBALS['_composer_autoload_path'];

$ignition = new Ignition();
$laravelApp = $ignition->turnOn(realpath(dirname($GLOBALS['_composer_autoload_path']).'/../'));
$ignition->run();
echo 'Server started'."\n";



$exitCode = $ignition->waitExitSignal();

if (!getenv('GEAR_DEV_SERVER')) {
    sleep(2);
}

echo 'Exited: ' . $exitCode . PHP_EOL;
exit($exitCode);