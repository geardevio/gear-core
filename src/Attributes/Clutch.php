<?php

namespace GearDev\Core\Attributes;

use GearDev\Collector\Collector\AttributeInterface;
use GearDev\Core\Warmers\ClutchInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Clutch implements AttributeInterface
{

    public function onClass(string $className, AttributeInterface $attribute): void
    {
        $instance = new $className;
        if (!is_a($instance, ClutchInterface::class)) {
            throw new \Exception('Class ' . $className . ' must implement ' . ClutchInterface::class);
        }
        $instance->clutch();
        unset($instance);
    }

    public function onMethod(string $className, string $methodName, AttributeInterface $attribute): void
    {
        // TODO: Implement onMethod() method.
    }

    public function onProperty(string $className, string $propertyName, AttributeInterface $attribute): void
    {
        // TODO: Implement onProperty() method.
    }
}