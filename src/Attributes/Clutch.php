<?php

namespace GearDev\Core\Attributes;

use GearDev\Collector\Collector\AttributeInterface;
use GearDev\Core\Warmers\ClutchInterface;
use Illuminate\Foundation\Application;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Clutch implements AttributeInterface
{

    public function onClass(Application $app, string $className, AttributeInterface $attribute): void
    {
        $instance = $app->make($className);
        if (!is_a($instance, ClutchInterface::class)) {
            throw new \Exception('Class ' . $className . ' must implement ' . ClutchInterface::class);
        }
        $instance->clutch($app);
        unset($instance);
    }

    public function onMethod(Application $app, string $className, string $methodName, AttributeInterface $attribute): void
    {
        // TODO: Implement onMethod() method.
    }

    public function onProperty(Application $app, string $className, string $propertyName, AttributeInterface $attribute): void
    {
        // TODO: Implement onProperty() method.
    }
}