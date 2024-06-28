<?php

namespace GearDev\Core\Attributes;

use GearDev\Collector\Collector\AttributeInterface;
use GearDev\Core\Warmers\WarmerInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Warmer implements AttributeInterface
{
    public function onClass(string $className, AttributeInterface $attribute): void
    {
        $instance = new $className;
        if (!is_a($instance, WarmerInterface::class)) {
            throw new \Exception('Class ' . $className . ' must implement ' . WarmerInterface::class);
        }
        $instance->warm();
        unset($instance);
    }

    public function onMethod(string $className, string $methodName, AttributeInterface $attribute): void
    {
        return;
    }

    public function onProperty(string $className, string $propertyName, AttributeInterface $attribute): void
    {
        return;
    }
}