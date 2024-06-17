<?php

namespace GearDev\Core\Starter;

use GearDev\Collector\Collector\Collector;
use GearDev\Core\Attributes\Clutch;
use GearDev\Core\Attributes\Warmer;
use GearDev\Core\ContextStorage\ContextStorage;
use GearDev\Coroutines\Co\ChannelFactory;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Bootstrap\SetRequestForConsole;
use ReflectionObject;

class Ignition
{
    private function bootEngine(string $baseDir): Application
    {
        return include $baseDir . '/bootstrap/app.php';
    }

    public function turnOn(string $basePath): Application
    {
        $app = $this->bootEngine($basePath);

        $this->bootstrap($app);

        Collector::getInstance()->collect(app_path());

        $this->warmEngine();
        ContextStorage::setCurrentRoutineName('main');
        ContextStorage::setApplication($app);
        return $app;
    }

    private function bootstrap(Application $app): void
    {
        $app->bootstrapWith($this->getBootstrappers($app));

        $app->loadDeferredProviders();
    }

    protected function getBootstrappers(Application $app): array
    {
        $method = (new ReflectionObject(
            $kernel = $app->make(HttpKernelContract::class)
        ))->getMethod('bootstrappers');

        $method->setAccessible(true);

        return $this->injectBootstrapperBefore(
            RegisterProviders::class,
            SetRequestForConsole::class,
            $method->invoke($kernel)
        );
    }

    protected function injectBootstrapperBefore(string $before, string $inject, array $bootstrappers): array
    {
        $injectIndex = array_search($before, $bootstrappers, true);

        if ($injectIndex !== false) {
            array_splice($bootstrappers, $injectIndex, 0, [$inject]);
        }

        return $bootstrappers;
    }

    private function warmEngine()
    {
        $this->wroomWroom();
    }

    private function wroomWroom()
    {
        $collector = Collector::getInstance();
        $collector->runAttributeInstructions(Warmer::class);
    }

    public function run() {
        $collector = Collector::getInstance();
        $collector->runAttributeInstructions(Clutch::class);
    }

    public function createExitChannel() {
        $exitControlChannel = ChannelFactory::createChannel(1);
        ContextStorage::setSystemChannel('exitChannel', $exitControlChannel);
        ContextStorage::setCurrentRoutineName('main');
        return $exitControlChannel;
    }

    /**
     * @return void
     * @deprecated when borned. Need to create better abstract solution
     */
    public function waitExitSignal(): int
    {
        if (
            class_exists(\Swow\Coroutine::class) &&
            class_exists(\Swow\Signal::class)
        ) {
            $exitControlChannel = $this->createExitChannel();
            \Swow\Coroutine::run(static function () use ($exitControlChannel): void {
                \Swow\Signal::wait(\Swow\Signal::INT);
                $exitControlChannel->push(\Swow\Signal::TERM);
            });
            \Swow\Coroutine::run(static function () use ($exitControlChannel): void {
                \Swow\Signal::wait(\Swow\Signal::TERM);
                $exitControlChannel->push(\Swow\Signal::TERM);
            });

            return $exitControlChannel->pop();
        } else {
            while (true) {
                sleep(2);
            }
        }
    }
}