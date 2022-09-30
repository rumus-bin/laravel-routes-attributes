<?php

namespace RumusBin\AttributesRouter;

use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RumusBin\AttributesRouter\RoteAttributes\Fallback;
use RumusBin\AttributesRouter\RoteAttributes\Route;
use RumusBin\AttributesRouter\RoteAttributes\RouteAttribute;
use RumusBin\AttributesRouter\RoteAttributes\ScopeBindings;
use RumusBin\AttributesRouter\RoteAttributes\Where;
use RumusBin\AttributesRouter\RoteAttributes\WhereAttribute;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class RouterLoader
{
    protected string $basePath;

    protected array $middlewares = [];

    protected string $rootNamespace;

    private  Router $router;

    public function __construct(
        Router $router
    ) {
        $this->router = $router;
        $this->useBasePath(app()->path());
    }

    public function group(array $options, $routes): self
    {
        $this->router->group($options, $routes);

        return $this;
    }

    public function setMiddleware(string | array $middleware): self
    {
        $this->middlewares = Arr::wrap($middleware);

        return $this;
    }

    public function useNamespace(string $namespace): self
    {
        $this->rootNamespace = $namespace;

        return $this;
    }

    public function registerDirectory(string | array $directories): void
    {
        $directories = Arr::wrap($directories);
        $files = (new Finder())->files()->name('*.php')->in($directories)->sortByName();

        collect($files)->each(fn (SplFileInfo $file) => $this->registerFile($file));

    }

    public function useBasePath(string $path): self
    {
        $this->basePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        return $this;
    }

    private function registerFile(string | SplFileInfo $file): void
    {
        if (is_string($file)) {
            $file = new SplFileInfo($file);
        }
        $fullyQualifiedClassName = $this->fullQualifiedFileNameFromFile($file);

        $this->processAttributes($fullyQualifiedClassName);
    }

    private function fullQualifiedFileNameFromFile(SplFileInfo | string $file): string
    {
        $class = trim(
            Str::replaceFirst($this->basePath, '', $file->getRealPath()),
            DIRECTORY_SEPARATOR
        );

        $class = str_replace(
            [DIRECTORY_SEPARATOR, 'App\\'],
            ['\\', app()->getNamespace()],
            ucfirst(Str::replaceLast('.php', '', $class))
        );

        return $this->rootNamespace . $class;
    }

    protected function processAttributes(string $className): void
    {
        if (! class_exists($className)) {
            return;
        }

        $reflection = new \ReflectionClass($className);

        $classRouteAttributes = new ClassRouteAttributes($reflection);

        if ($classRouteAttributes->resource()) {
            $this->registerResource($reflection, $classRouteAttributes);
        }

        $groups = $classRouteAttributes->groups();

        foreach ($groups as $group) {
            $router = $this->router;
            $router->group($group, fn () => $this->registerRoutes($reflection, $classRouteAttributes));
        }
    }

    public function useRootNamespace(string $namespace): self
    {
        $this->rootNamespace = rtrim(str_replace('/', '\\', $namespace), '\\') . '\\';

        return $this;
    }

    protected function registerResource(\ReflectionClass $class, ClassRouteAttributes $classRouteAttributes): void
    {
        $this->router->group(
            [
                'domain' => $classRouteAttributes->domain(),
                'prefix' => $classRouteAttributes->prefix()
            ], function () use ($class, $classRouteAttributes) {
                $route = $classRouteAttributes->apiResource() ?
                    $this->router->apiResource($classRouteAttributes->resource(), $class->getName()) :
                    $this->router->resource($classRouteAttributes->resource(), $class->getName());
                if ($only = $classRouteAttributes->only()) {
                    $route->only($only);
                }
                if ($except = $classRouteAttributes->except()) {
                    $route->except($except);
                }
                if ($names = $classRouteAttributes->names()) {
                    $route->names($names);
                }

                $route->middleware([...$this->middlewares, ...$classRouteAttributes->middleware()]);
            }
        );
    }

    protected function registerRoutes(\ReflectionClass $class, ClassRouteAttributes $classRouteAttributes): void
    {
        foreach ($class->getMethods() as $method) {
            $attributes = $method->getAttributes(RouteAttribute::class, \ReflectionAttribute::IS_INSTANCEOF);
            $wheresAttributes = $method
                ->getAttributes(WhereAttribute::class, \ReflectionAttribute::IS_INSTANCEOF);
            $fallbackAttribute = $method
                ->getAttributes(Fallback::class, \ReflectionAttribute::IS_INSTANCEOF);
            $scopeBindingAttributes = $method->getAttributes(
                ScopeBindings::class,
                \ReflectionAttribute::IS_INSTANCEOF
            )[0] ?? null;

            foreach ($attributes as $attribute) {
                try {
                    $attributeClass = $attribute->newInstance();
                } catch (\Throwable) {
                    continue;
                }

                if (! $attributeClass instanceof Route) {
                    continue;
                }

                $httpMethods = $attributeClass->methods;

                $action = $method->getName() === '__invoke' ?
                    $class->getName() :
                    [$class->getName(), $method->getName()];

                $route = $this->router->addRoute($httpMethods, $attributeClass->uri, $action)
                    ->name($attributeClass->name);

                if ($scopeBindingAttributes) {
                    /** @var ScopeBindings $scopeBindingAttributesClass */
                    $scopeBindingAttributesClass = $scopeBindingAttributes->newInstance();

                    if ($scopeBindingAttributesClass->scopeBindings) {
                        $route->scopeBindings();
                    }
                } elseif ($classRouteAttributes->scopeBindings()) {
                    $route->scopeBindings();
                }

                $wheres = $classRouteAttributes->wheres();

                foreach ($wheresAttributes as $wheresAttribute) {
                    /** @var Where $wheresAttributeClass */
                    $wheresAttributeClass = $wheresAttribute->newInstace();

                    $wheres[$wheresAttributeClass->param] = $wheresAttributeClass->constraint;
                }
                if (! empty($wheres)) {
                    $route->setWheres($wheres);
                }

                $classMiddleware = $classRouteAttributes->middleware();
                $methodMiddleware = $attributeClass->middleware;
                $route->middleware([...$this->middlewares, ...$classMiddleware, ...$methodMiddleware]);

                if (count($fallbackAttribute)) {
                    $route->fallback();
                }
            }
        }
    }
}
