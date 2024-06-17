<?php

namespace GearDev\Core\ContextStorage;

use GearDev\Coroutines\Co\CoManagerFactory;
use GearDev\Coroutines\Interfaces\ChannelInterface;
use Illuminate\Foundation\Application;


class ContextStorage
{
    static private array $storage = [
        'systemChannels' => [],
        'containers' => [],
        'interStreamInstances' => [],
        'maskoStrings' => [],
        'logContext' => []
    ];

    public static function setMaskoString(string $value, string $type = 'default')
    {
        self::$storage['maskoStrings'][$value] = $type;
    }

    public static function addLogContext($key, $value)
    {
        $coroutineId = CoManagerFactory::getCoroutineManager()->getCurrentCoroutineId();
        self::$storage['logContext'][$coroutineId][$key] = $value;
    }

    public static function getLogContext()
    {
        $coroutineId = CoManagerFactory::getCoroutineManager()->getCurrentCoroutineId();
        return self::$storage['logContext'][$coroutineId] ?? [];
    }

    public static function cloneLogContextFromFirstCoroutineToSecond(int $firstCoroutineId, int $secondCoroutineId)
    {
        if (!isset(self::$storage['logContext'][$firstCoroutineId])) {
            self::$storage['logContext'][$secondCoroutineId] = [];
            return;
        }
        self::$storage['logContext'][$secondCoroutineId] = self::$storage['logContext'][$firstCoroutineId];
    }

    public static function getMaskoStrings()
    {
        return self::$storage['maskoStrings'];
    }

    public static function setInterStreamInstance($abstract, $instance)
    {
        self::$storage['interStreamInstances'][$abstract] = $instance;
    }

    public static function getInterStreamInstances()
    {
        return self::$storage['interStreamInstances'];
    }

    public static function removeSystemChannel(string $name): void
    {
        unset(self::$storage['systemChannels'][$name]);
    }

    public static function setSystemChannel(string $name, ChannelInterface $channel): void
    {
        if (array_key_exists($name, self::$storage['systemChannels'] ?? [])) {
            throw new \Exception(sprintf('Channel with name %s already exists', $name));
        }
        self::$storage['systemChannels'][$name] = $channel;
    }

    public static function getSystemChannel(string $name): ChannelInterface
    {
        if (!array_key_exists($name, self::$storage['systemChannels'] ?? [])) {
            throw new \Exception(sprintf('Channel with name %s does not exist', $name));
        }
        return self::$storage['systemChannels'][$name];
    }

    static public function set(string $key, $value): void
    {
        $coroutineId = CoManagerFactory::getCoroutineManager()->getCurrentCoroutineId();
        self::$storage[$coroutineId][$key] = $value;
    }

    static public function get(string $key)
    {
        $coroutineId = CoManagerFactory::getCoroutineManager()->getCurrentCoroutineId();
        return self::$storage[$coroutineId][$key] ?? null;
    }

    public static function dump(string $message = '')
    {
        $coroutineId = CoManagerFactory::getCoroutineManager()->getCurrentCoroutineId();
        var_dump($message, self::$storage[$coroutineId] ?? []);
    }

    public static function clearStorage(): void
    {
        $coroutineId = CoManagerFactory::getCoroutineManager()->getCurrentCoroutineId();
        unset(self::$storage[$coroutineId]);
        unset(self::$storage['containers'][$coroutineId]);
        unset(self::$storage['routineNames'][$coroutineId]);
        unset(self::$storage['logContext'][$coroutineId]);
    }

    public static function setApplication(\Illuminate\Contracts\Container\Container|Application $application, int $coroutineId = null): void
    {
        $coroutineId = $coroutineId ?? CoManagerFactory::getCoroutineManager()->getCurrentCoroutineId();
        if (!self::getCurrentRoutineName()) {
            throw new \Exception('Routine name is not set');
        }
        self::$storage['routineNames'][$coroutineId] = self::getCurrentRoutineName();
        self::$storage['containers'][$coroutineId] = $application;
    }

    public static function setCurrentRoutineName(string $name): void
    {
        $coroutineId = CoManagerFactory::getCoroutineManager()->getCurrentCoroutineId();
        self::$storage['routineNames'][$coroutineId] = $name;
    }

    public static function getCurrentRoutineName()
    {
        $coroutineId = CoManagerFactory::getCoroutineManager()->getCurrentCoroutineId();
        return self::$storage['routineNames'][$coroutineId] ?? null;
    }

    public static function getMainApplication(): ?Application
    {
        $coroutineId = CoManagerFactory::getCoroutineManager()->getCurrentCoroutineId();
        if (!isset(self::$storage['containers'][$coroutineId])) throw new \Exception('Main application not found');
        $mainApp = self::$storage['containers'][$coroutineId];
        if (!$mainApp) throw new \Exception('Main application not found');
        return $mainApp;
    }

    public static function getApplication(): ?Application
    {
        $coroutineId = CoManagerFactory::getCoroutineManager()->getCurrentCoroutineId();
        return self::$storage['containers'][$coroutineId] ?? null;
    }

    public static function getStorageCountForMetric(): int
    {
        return count(self::$storage) - 2;
    }

    public static function getContainersCountForMetric(): int
    {
        return count(self::$storage['containers']);
    }
}