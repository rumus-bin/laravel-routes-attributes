<?php

namespace RumusBin\AttributesRouter\RoteAttributes;

use Illuminate\Routing\Router;
use Illuminate\Support\Arr;

class Route implements RouteAttribute
{
    public array $methods;

    public array $middleware;

    public function __construct(
        array | string $methods,
        public string $uri,
        public ?string $name = null,
        array | string $middleware = [],
    ) {
        $this->methods = array_map(
            static fn (string $verb) => in_array(
                $upperVerb = strtoupper($verb),
                Router::$verbs
            )
                ? $upperVerb
                : $verb,
            Arr::wrap($methods)
        );
        $this->middleware = Arr::wrap($middleware);
    }
}
