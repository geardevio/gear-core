<?php

namespace GearDev\Core\Warmers;

use Illuminate\Foundation\Application;

interface WarmerInterface
{
    public function warm(Application $app): void;
}