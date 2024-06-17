<?php

namespace GearDev\Core;

use GearDev\Collector\Collector\Collector;

class GearCoreLaravelProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot() {

    }

    public function register() {
        Collector::addPackageToCollector(__DIR__);
    }

}