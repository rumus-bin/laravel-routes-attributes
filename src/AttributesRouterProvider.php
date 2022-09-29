<?php

namespace RumusBin\AttributesRouter;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class AttributesRouterProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../config/attribute-router.php'
                ],
                'config'
            );
        }

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        if (! $this->shouldRegisterRoutes()) {
            return;
        }
        $routerRegister = (new RouterLoader(app()->router))
            ->setMiddleware(config('attribute-router.middleware') ?? []);

        collect($this->getRouteDirectories())->each(
            function (string|array $directory, string|int $namespace) use ($routerRegister) {
                if (is_array($directory)) {
                    $options = Arr::except($directory['namespace'], ['namespace', 'base_path']);

                    $routerRegister->useNamespace($directory['namespace'] ?? app()->getNamespace())
                        ->useBasePath(
                            $directory['base_path'] ??
                                (
                                    isset($directory['namespace']) ?
                                    $namespace :
                                    app()->path()
                                )
                        )
                        ->group($options, fn () => $routerRegister->registerDirectory($namespace));
                } else {
                    is_string($namespace) ?
                        $routerRegister
                            ->useRootNamespace($namespace)
                            ->useBasePath($directory)
                            ->registerDirectory($directory) :
                        $routerRegister
                            ->useRootNamespace(app()->getNamespace())
                            ->useBasePath(app()->path())
                            ->registerDirectory($directory);
                }
            }
        );
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/attribute-router.php', 'attribute-router');
    }

    private function shouldRegisterRoutes(): bool
    {
        if (! config('attribute-router.enabled')) {
            return false;
        }

        if ($this->app->routesAreCached()) {
            return false;
        }

        return true;
    }

    protected function getRouteDirectories(): array
    {
        return config('attribute-router.directories');
    }
}
