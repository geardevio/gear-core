<?php

namespace GearDev\Core\Attributes;

use GearDev\Collector\Collector\AttributeInterface;
use GearDev\Core\Warmers\WarmerInterface;
use Illuminate\Foundation\Application;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Warmer implements AttributeInterface
{
    public function onClass(Application $app, string $className, AttributeInterface $attribute): void
    {
        $instance = $app->make($className);
        if (!is_a($instance, WarmerInterface::class)) {
            throw new \Exception('Class ' . $className . ' must implement ' . WarmerInterface::class);
        }
        $instance->warm($app);
        unset($instance);
    }

    public function onMethod(Application $app, string $className, string $methodName, AttributeInterface $attribute): void
    {
        return;
    }

    public function onProperty(Application $app, string $className, string $propertyName, AttributeInterface $attribute): void
    {
        return;
    }
}