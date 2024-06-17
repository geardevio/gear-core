<?php

namespace GearDev\Core\Warmers;

use Illuminate\Foundation\Application;

interface ClutchInterface
{
    public function clutch(Application $app): void;
}